<?php

namespace ProfilePress\Libsodium\SocialLogin;

use Hybridauth\Exception\RuntimeException;
use Hybridauth\Storage\StorageInterface;
use ProfilePress\Core\Classes\PPRESS_Session;

/**
 * HybridAuth storage manager
 */
class PPSessionAdapter implements StorageInterface
{
    /**
     * Namespace
     *
     * @var string
     */
    protected $bucket;

    /**
     * Key prefix
     *
     * @var string
     */
    protected $keyPrefix = '';

    /**
     * Initiate a new session
     *
     */
    public function __construct()
    {
        $this->bucket = PPRESS_Session::get_instance();
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $key = $this->keyPrefix . strtolower($key);

        return $this->bucket->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $key = $this->keyPrefix . strtolower($key);

        $this->bucket->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $key = $this->keyPrefix . strtolower($key);

        $this->bucket->set($key, null);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMatch($key)
    {
    }
}
