<?php
/** The name of the keyspace to use */
define('CASSANDRA_KEYSPACE', 'BlogExampleWebkonf');

// We create the main connection with one server.
Cassandra::createInstance(array(
        'host' => '127.0.0.1',
        'port' => 9160,
        'use-framed-transport' => true,
        'send-timeout-ms' => 1000,
        'receive-timeout-ms' => 1000
    )
);

