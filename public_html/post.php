<?php
/**
 * Single post display page
 *
 * @author Zsolt Szeberényi
 * @license public domain
 */

require '../lib/Cassandra/Cassandra.php';
require '../config.php';

header('Content-type: text/html; charset=utf-8');

if (empty($_GET['id'])) {
    // We didn't get a post ID, redirect to the post list
    header('Location: /');
    exit;
}

// Retrieve the post's data from Cassandra
$cassandra = Cassandra::getInstance();
$cassandra->useKeyspace(CASSANDRA_KEYSPACE);
$post = $cassandra->cf('posts')->get((int)$_GET['id']);

if (empty($post)) {
    // We couldn't retrieve the post, redirect to the post list
    header('Location: /');
    exit;
}

?><!DOCTYPE html>
<html>
<head>
<title>Demo blog</title>
</head>

<body>
    <h1>Demo blog</h1>
    <?php
        $tags = explode(',', $post['tags']);
    ?>
    <div class="post">
        <h2><?php echo $post['title']; ?></h2>
        <div>
            <span class="author"><a href="index.php?author=<?php echo urlencode($post['author']); ?>"><?php echo $post['author']; ?></a></span>
            <span class="date"><?php echo $post['date']; ?></span>
        </div>
        <div class="tags">
        <?php foreach($tags as $tag): ?>
            <a href="index.php?tag=<?php echo urlencode($tag); ?>"><?php echo $tag ?></a>
        <?php endforeach; ?>
        </div>
        <div class="content">
            <?php echo $post['content']; ?>
        </div>
    </div>
</body>
</html>