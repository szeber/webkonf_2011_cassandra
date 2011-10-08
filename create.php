<?php
/**
 * Creates the schema for the demo project.
 *
 * As the first step it destroys the keyspace with the name defined in CASSANDRA_KEYSPACE {@uses CASSANDRA_KEYSPACE},
 * so be careful.
 *
 * @author Zsolt Szeberényi
 * @license public domain
 */

require 'lib/Cassandra/Cassandra.php';
require 'config.php';

// Get the cassandra connection instance
$cassandra = Cassandra::getInstance();

// Drop the keyspace destroying all data in it
try {
    $cassandra->dropKeyspace(CASSANDRA_KEYSPACE);
} catch (Exception $e) {
    // We don't care if the keyspace doesn't exist, so do nothing in case of an exception
}

// CReate and use the keyspace
$cassandra->createKeyspace(CASSANDRA_KEYSPACE);
$cassandra->useKeyspace(CASSANDRA_KEYSPACE);

// Set the maximum retry count for the commands.
$cassandra->setMaxCallRetries(5);

// Create the posts column family, specifying the metadata for the columns
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
            'index-type' => Cassandra::INDEX_KEYS, // Also add a secondary index to the author field
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

// Create the timeline column family
$cassandra->createStandardColumnFamily(
    CASSANDRA_KEYSPACE,
    'timeline'
);

// Create the index column family
$cassandra->createStandardColumnFamily(
    CASSANDRA_KEYSPACE,
    'indexes'
);
