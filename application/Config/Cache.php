<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache store that will be used by the
    | framework. This connection is utilized if another isn't explicitly
    | specified when running a cache operation inside the application.
    | Should use Level Redis > Memcached > File
    |
    */

    'default' => 'file',

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    */

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => PATH_WRITE . '/cache/objects',
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => 0,
            'timeout' => 5.0,
            'read_timeout' => 5.0,
            'persistent' => false,
        ],

        // 'memcached' => [
        //     'driver' => 'memcached',
        //     'servers' => [
        //         ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 0]
        //     ],
        //     'compression' => true,
        //     'serializer' => \Memcached::SERIALIZER_PHP,
        //     'binary_protocol' => true,
        // ],

        'array' => [
            'driver' => 'array',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing the APC, database, memcached, Redis, and DynamoDB cache
    | stores, there might be other applications using the same cache. For
    | that reason, you may prefix every cache key to avoid collisions.
    |
    */

    'prefix' => 'cmsff',

    /*
    |--------------------------------------------------------------------------
    | Rate Limiter Store
    |--------------------------------------------------------------------------
    |
    | Specify which cache store should be used for rate limiting operations.
    | Recommended: 'redis' for best performance, 'database' for simplicity
    |
    */

    'limiter' => 'file', // or 'redis' for production

];
