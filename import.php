<?php
/**
 * Imports the data from posts.php and loads it into Cassandra.
 *
 * @author Zsolt Szeberényi
 * @license public domain
 */

require 'lib/Cassandra/Cassandra.php';
require 'config.php';
require 'data/posts.php';

$cassandra = Cassandra::getInstance();

$cassandra->useKeyspace(CASSANDRA_KEYSPACE);

$allTags = array();
foreach($posts as $post) {
    $cassandra->set('posts.' . $post['id'], $post);

    $tags = explode(',', $post['tags']);
    $cassandra->set('timeline.main', array($post['date'] => $post['id']));
    foreach($tags as $tag) {
        $allTags[$tag] = 1;
        $cassandra->set('timeline.' . $tag, array($post['date'] => $post['id']));
    }
}

$cassandra->set('indexes.all_tags', $allTags);
