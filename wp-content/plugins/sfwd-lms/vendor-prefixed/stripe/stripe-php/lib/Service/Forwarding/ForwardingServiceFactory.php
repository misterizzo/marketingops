<?php

// File generated from our OpenAPI spec

namespace StellarWP\Learndash\Stripe\Service\Forwarding;

/**
 * Service factory class for API resources in the Forwarding namespace.
 *
 * @property RequestService $requests
 */
class ForwardingServiceFactory extends \StellarWP\Learndash\Stripe\Service\AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = [
        'requests' => RequestService::class,
    ];

    protected function getServiceClass($name)
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
