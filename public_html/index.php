<?php

require '../lib/Cassandra/Cassandra.php';
require '../config.php';

header('Content-type: text/html; charset=utf-8');

define('LIST_LIMIT', 10);
Cassandra::getInstance()->useKeyspace(CASSANDRA_KEYSPACE);

function listByAuthor($name) {
    $results =  Cassandra::getInstance()->cf('posts')->getWhere(array('author' => $name))->getAll();

    usort($results, 'dateSort');

    return array_reverse($results);
}

function dateSort($a, $b) {
    if ($a['date'] == $b['date']) {
        return 0;
    }
    return ($a['date'] < $b['date'] ? -1 : 1);
}

function listByTimeline($name) {
    if (empty($_GET['from'])) {
        $startFrom = null;
    } else {
        $startFrom = $_GET['from'];
    }

    $cassandra =  Cassandra::getInstance();

    $timeline = $cassandra->cf('timeline')->get($name, null, $startFrom, null, true, LIST_LIMIT +1);

    return $cassandra->cf('posts')->getMultiple(array_values($timeline));
}


if (!empty($_GET['author'])) {
    $posts = listByAuthor($_GET['author']);
    $baseLink = 'index.php?author=' . urlencode($_GET['author']);
} elseif (!empty($_GET['tag'])) {
    $posts = listByTimeline($_GET['tag']);
    $baseLink = 'index.php?tag=' . urlencode($_GET['tag']);
} else {
    $posts = listByTimeline('main');
    $baseLink = 'index.php?tag=main';
}
if (count($posts) > LIST_LIMIT) {
    $nextPost = array_pop($posts);
    $nextPostDate = $nextPost['date'];
} else {
    $nextPostDate = null;
}

?><!DOCTYPE html>
<html>
<head>
<title>Demo blog</title>
</head>

<body>
    <h1>Demo blog</h1>
    <?php
        foreach($posts as $post):
            $tags = explode(',', $post['tags']);
    ?>
    <div class="post">
        <h2><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></h2>
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
            <?php echo mb_substr(strip_tags($post['content'], '<br><br/><p>'), 0, 500, 'utf-8'); ?>...
        </div>
        <p><a href="post.php?id=<?php echo $post['id']; ?>">Tovább</a></p>
    </div>
    <?php endforeach; ?>
    <?php if ($nextPostDate): ?>
        <p><a href="<?php echo $baseLink . '&amp;from=' . $nextPostDate; ?>">Régebbi postok</a></p>
    <?php endif; ?>
</body>
</html>