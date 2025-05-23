<?php
/**
 * Copyright © 2019-2025 Rhubarb Tech Inc. All Rights Reserved.
 *
 * The Object Cache Pro Software and its related materials are property and confidential
 * information of Rhubarb Tech Inc. Any reproduction, use, distribution, or exploitation
 * of the Object Cache Pro Software and its related materials, in whole or in part,
 * is strictly forbidden unless prior permission is obtained from Rhubarb Tech Inc.
 *
 * In addition, any reproduction, use, distribution, or exploitation of the Object Cache Pro
 * Software and its related materials, in whole or in part, is subject to the End-User License
 * Agreement accessible in the included `LICENSE` file, or at: https://objectcache.pro/eula
 */

declare(strict_types=1);

namespace RedisCachePro\Connections;

use Throwable;
use RedisSentinel;

use RedisCachePro\Clients\PhpRedisSentinel;

use RedisCachePro\Configuration\Configuration;
use RedisCachePro\Connectors\PhpRedisConnector;
use RedisCachePro\Exceptions\ConnectionException;

use function RedisCachePro\log;

class PhpRedisSentinelsConnection extends PhpRedisReplicatedConnection implements ConnectionInterface
{
    use Concerns\SentinelsConnection;

    /**
     * The current Sentinel node.
     *
     * @var string
     */
    protected $sentinel;

    /**
     * Holds all Sentinel states and URLs.
     *
     * If the state is `null` no connection has been established.
     * If the state is `false` the connection failed or a timeout occurred.
     * If the state is a `PhpRedisSentinel` object it's the current Sentinel node.
     *
     * @var array<string, \RedisCachePro\Clients\PhpRedisSentinel|null|false>
     */
    protected $sentinels;

    /**
     * Create a new PhpRedis Sentinel connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;

        foreach ($config->sentinels as $sentinel) {
            $this->sentinels[$sentinel] = null;
        }

        if (PhpRedisConnector::supports('get-meta')) {
            $this->supportsGetWithMeta = true;
        }

        $this->connectToSentinels();
    }

    /**
     * Returns the current Sentinel connection.
     *
     * @return \RedisCachePro\Clients\PhpRedisSentinel|false|null
     */
    public function sentinel()
    {
        return $this->sentinels[$this->sentinel];
    }

    /**
     * Establish a connection to the given Redis Sentinel and its primary and replicas.
     *
     * @param  string  $url
     * @return void
     */
    protected function establishConnections(string $url)
    {
        $version = (string) \phpversion('redis');

        $config = clone $this->config;
        $config->setUrl($url);

        $persistentId = '';

        $arguments = [
            'host' => $config->host,
            'port' => $config->port,
            'connectTimeout' => $config->timeout,
            'persistent' => $persistentId,
            'retryInterval' => $config->retry_interval,
            'readTimeout' => $config->read_timeout,
        ];

        if ($config->password) {
            $arguments['auth'] = $config->username
                ? [$config->username, $config->password]
                : $config->password;
        }

        if ($config->tls_options && version_compare($version, '6.0', '>=')) {
            $arguments['ssl'] = $config->tls_options;
        }

        $this->sentinels[$url] = new PhpRedisSentinel(function () use ($arguments, $version) {
            return version_compare($version, '6.0', '<')
                ? new RedisSentinel(...array_values($arguments))
                : new RedisSentinel($arguments);
        }, $config->tracer);

        $this->discoverPrimary();
        $this->discoverReplicas();
    }

    /**
     * Discovers and connects to the Sentinel primary.
     *
     * @return void
     */
    protected function discoverPrimary()
    {
        if (! $sentinel = $this->sentinel()) {
            throw new ConnectionException(sprintf(
                'Unable to discover sentinel primary for `%s` (%s)',
                $this->sentinel,
                gettype($this->sentinel)
            ));
        }

        if (! $primary = $sentinel->getMasterAddrByName($this->config->service)) {
            throw new ConnectionException("Failed to retrieve sentinel primary of `{$this->sentinel}`");
        }

        $config = clone $this->config;
        $config->setHost($primary[0]);
        $config->setPort($primary[1]);

        $connection = PhpRedisConnector::connectToInstance($config);

        /** @var array<int, mixed> $role */
        $role = $connection->role();

        if (($role[0] ?? null) !== 'master') {
            throw new ConnectionException("Sentinel primary of `{$this->sentinel}` is not a primary");
        }

        $this->primary = $connection;
    }

    /**
     * Discovers and connects to the Sentinel replicas.
     *
     * @return void
     */
    protected function discoverReplicas()
    {
        if (! $sentinel = $this->sentinel()) {
            throw new ConnectionException(sprintf(
                'Unable to discover sentinel replicas for `%s` (%s)',
                $this->sentinel,
                gettype($this->sentinel)
            ));
        }

        if (! $replicas = $sentinel->slaves($this->config->service)) {
            throw new ConnectionException("Failed to discover Sentinel replicas of `{$this->sentinel}`");
        }

        $this->replicas = [];

        foreach ($replicas as $replica) {
            if (($replica['role-reported'] ?? '') !== 'slave') {
                continue;
            }

            $config = clone $this->config;
            $config->setHost($replica['ip']);
            $config->setPort($replica['port']);

            try {
                $this->replicas[$replica['name']] = PhpRedisConnector::connectToInstance($config);
            } catch (Throwable $error) {
                log('warning', $error->getMessage());
            }
        }
    }
}
