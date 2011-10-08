<?php
/**
 * Post listing page.
 *
 * @author Zsolt Szeberényi
 * @license public domain
 */

require '../lib/Cassandra/Cassandra.php';
require '../config.php';

header('Content-type: text/html; charset=utf-8');

/** The number of posts to be displayed on a page */
define('LIST_LIMIT', 10);

Cassandra::getInstance()->useKeyspace(CASSANDRA_KEYSPACE);

/**
 * Returns all of the posts created by the author ordered by the creation date.
 *
 * @param string   The name of the author, we are looking for.
 *
 * @return array   The posts created by the author.
 */
function listByAuthor($name) {
    $results =  Cassandra::getInstance()->cf('posts')->getWhere(array('author' => $name))->getAll();

    // When using secondary indexes, the data is going to be in random order (in the case of RandomPartitioner),
    // so sort it by date.
    usort($results, 'dateSort');

    // Return the posts with the latest post first.
    return array_reverse($results);
}

/**
 * usort function to sort the posts by date
 *
 * @param array $a
 * @param array $b
 *
 * @return int
 */
function dateSort(array $a, array $b) {
    if ($a['date'] == $b['date']) {
        return 0;
    }
    return ($a['date'] < $b['date'] ? -1 : 1);
}

/**
 * Returns the list of posts in the specified timeline sorted by the creation date.
 *
 * The function returns LIST_LIMIT + 1 posts in descending order. The offset is set by the 'from' GET param.
 *
 * @param string $name   The name of the timeline.
 *
 * @return array   The posts in the timeline.
 */
function listByTimeline($name) {
    // Set up the offset
    if (empty($_GET['from'])) {
        $startFrom = null;
    } else {
        $startFrom = $_GET['from'];
    }

    $cassandra =  Cassandra::getInstance();

    // Get the list of posts in the timeline
    $timeline = $cassandra->cf('timeline')->get($name, null, $startFrom, null, true, LIST_LIMIT +1);

    // Return the data for the posts retrieved from the timeline
    return $cassandra->cf('posts')->getMultiple(array_values($timeline));
}

if (!empty($_GET['author'])) {
    // Author post list
    $posts = listByAuthor($_GET['author']);
    $baseLink = 'index.php?author=' . urlencode($_GET['author']);
} elseif (!empty($_GET['tag'])) {
    // Tag post list
    $posts = listByTimeline($_GET['tag']);
    $baseLink = 'index.php?tag=' . urlencode($_GET['tag']);
} else {
    // Main page post list (use 'main' timeline
    $posts = listByTimeline('main');
    $baseLink = 'index.php?tag=main';
}

if (count($posts) > LIST_LIMIT) {
    // We gpt more posts, then the LIST_LIMIT, so we have (at least) one more page. Set up the variable for the pager
    // offset, and remove the last post from the array.
    $nextPost = array_pop($posts);
    $nextPostDate = $nextPost['date'];
} else {
    // There are no more pages.
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