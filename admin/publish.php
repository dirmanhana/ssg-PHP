<?php
session_start();
require '../app/Models/PostModel.php';

// Set Timezone to Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');
require 'auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = trim($_POST['category'] ?? 'Uncategorized');

    // Process tags: explode, trim, filter empty
    $tagsInput = $_POST['tags'] ?? '';
    $tags = array_filter(array_map('trim', explode(',', $tagsInput)), function ($value) {
        return $value !== '';
    });
    // Re-index array to avoid gaps if needed (json_encode handles it but usually good to have list)
    $tags = array_values($tags);

    $id = $_POST['id'];

    $file = __DIR__ . '/../storage/posts.json';
    $posts = json_decode(file_get_contents($file), true);

    if ($id) {
        // Update existing post
        foreach ($posts as &$post) {
            if ($post['id'] == $id) {
                // Update slug if title changed (optional, but good practice to keep them synced or keep old slug?)
                // For simplicity, let's regenerate slug to match title
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

                // Handle Published Date
                $published_at = $_POST['published_at'];
                if (empty($published_at)) {
                    $published_at = date('Y-m-d H:i:s');
                } else {
                    $published_at = date('Y-m-d H:i:s', strtotime($published_at));
                }

                // Handle Image Upload
                $featured_image = trim($_POST['featured_image'] ?? ''); // Default to URL input
                if (isset($_FILES['featured_image_upload']) && $_FILES['featured_image_upload']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../public/uploads/posts/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileExt = strtolower(pathinfo($_FILES['featured_image_upload']['name'], PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (in_array($fileExt, $allowedExts)) {
                        $newFileName = $slug . '-' . time() . '.' . $fileExt;
                        $uploadFile = $uploadDir . $newFileName;

                        if (move_uploaded_file($_FILES['featured_image_upload']['tmp_name'], $uploadFile)) {
                            $featured_image = '/uploads/posts/' . $newFileName;
                        }
                    }
                }
                $image_alt = trim($_POST['image_alt'] ?? '');
                $summary = trim($_POST['summary'] ?? '');
                $post['title'] = $title;
                $post['content'] = $content;
                $post['slug'] = $slug;
                $post['tags'] = $tags;
                $post['category'] = $category; // Add category to post
                $post['featured_image'] = $featured_image; // Use the processed featured_image
                $post['image_alt'] = $image_alt;
                $post['summary'] = $summary;
                $post['published_at'] = $published_at;
                // $post['updated_at'] = date('Y-m-d'); // Could add this
                break;
            }
        }
        unset($post); // break reference
    } else {
        // Create new post
        // Create new post
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

        // Handle Published Date (New Post)
        $published_at = $_POST['published_at'];
        if (empty($published_at)) {
            $published_at = date('Y-m-d H:i:s');
        } else {
            $published_at = date('Y-m-d H:i:s', strtotime($published_at));
        }

        // Handle Image Upload for New Post
        if (isset($_FILES['featured_image_upload']) && $_FILES['featured_image_upload']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/posts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExt = strtolower(pathinfo($_FILES['featured_image_upload']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($fileExt, $allowedExts)) {
                $newFileName = $slug . '-' . time() . '.' . $fileExt;
                $uploadFile = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['featured_image_upload']['tmp_name'], $uploadFile)) {
                    $featured_image = '/uploads/posts/' . $newFileName;
                }
            }
        }

        $newId = empty($posts) ? 1 : end($posts)['id'] + 1; // Determine new ID
        $newPost = [
            'id' => $newId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'tags' => $tags,
            'category' => $category,
            'featured_image' => $featured_image,
            'image_alt' => $image_alt,
            'summary' => $summary,
            'published_at' => $published_at
        ];
        $posts[] = $newPost;
    }

    file_put_contents($file, json_encode($posts, JSON_PRETTY_PRINT));

    // Rebuild static site
    $output = [];
    $returnVar = 0;
    exec('cd ' . __DIR__ . '/../ && php build.php 2>&1', $output, $returnVar);

    if ($returnVar !== 0) {
        // Log error or display it
        error_log("Build failed: " . implode("\n", $output));
        echo "Build failed. Code: $returnVar. Output: " . implode("\n", $output);
        exit;
    }

    // Redirect to the Clean URL (root relative), assuming .htaccess handles the rewrite
    // If the site is at root, it's just /$slug
    header('Location: /' . $slug);
    exit;
}
