<?php

require 'lib/Cassandra/Cassandra.php';
require 'config.php';

$cassandra = Cassandra::getInstance();

try {
    $cassandra->dropKeyspace(CASSANDRA_KEYSPACE);
} catch (Exception $e) {}

$cassandra->createKeyspace(CASSANDRA_KEYSPACE);
$cassandra->useKeyspace(CASSANDRA_KEYSPACE);

$cassandra->setMaxCallRetries(5);

$cassandra->createStandardColumnFamily(
    CASSANDRA_KEYSPACE,
    'posts',
    array(
        array(
            'name' => 'date',
            'type' => Cassandra::TYPE_UTF8,
        ),
        array(
            'name' => 'author',
            'type' => Cassandra::TYPE_UTF8,
            'index-type' => Cassandra::INDEX_KEYS,
            'index-name' => 'AuthorIdx',
        ),
        array(
            'name' => 'title',
            'type' => Cassandra::TYPE_UTF8,
        ),
        array(
            'name' => 'content',
            'type' => Cassandra::TYPE_UTF8,
        ),
    )
);

$cassandra->createStandardColumnFamily(
    CASSANDRA_KEYSPACE,
    'timeline'
);

$cassandra->createStandardColumnFamily(
    CASSANDRA_KEYSPACE,
    'indexes'
);
