<?php
require 'auth.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $file = __DIR__ . '/../storage/posts.json';
    $posts = json_decode(file_get_contents($file), true);

    $newPosts = [];
    $deletedSlug = '';

    foreach ($posts as $post) {
        if ($post['id'] == $id) {
            $deletedSlug = $post['slug'];
            continue; // Skip this one
        }
        $newPosts[] = $post;
    }

    // Save updated JSON
    file_put_contents($file, json_encode($newPosts, JSON_PRETTY_PRINT));

    // Remove the static file
    if ($deletedSlug) {
        $staticFile = __DIR__ . '/../public/static/posts/' . $deletedSlug . '.html';
        if (file_exists($staticFile)) {
            unlink($staticFile);
        }
    }

    // Rebuild static site (to update index lists if we had one, though verifying we don't strictly need to for singular post deletion if we manually unlinked, but strictly safer to keep state consistent)
    // Actually, build.php only builds posts, it doesn't currently build an index list page. But running it is harmless.
    exec('cd ' . __DIR__ . '/../ && php build.php');

    header('Location: index.php');
    exit;
}
