<?php
// Ensure variables are set (redundant check if included correctly but safe)
if (!isset($post)) {
  // Fallback or error handling if accessed directly
  die('Direct access not allowed');
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($post['title']) ?></title>


  <meta name="description" content="<?= htmlspecialchars($post['summary'] ?? substr(strip_tags($content), 0, 150)) ?>">
  <meta name="author" content="<?= htmlspecialchars($settings['site_name']) ?>">

  <!-- Open Graph -->
  <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>">
  <meta property="og:description"
    content="<?= htmlspecialchars($post['summary'] ?? substr(strip_tags($content), 0, 150)) ?>">
  <meta property="og:type" content="article">
  <meta property="og:url" content="<?= rtrim($settings['site_url'], '/') . '/' . $post['slug'] ?>">
  <?php if (!empty($post['featured_image'])): ?>
    <meta property="og:image" content="<?= rtrim($settings['site_url'], '/') . $post['featured_image'] ?>">
  <?php endif; ?>
  <meta property="og:image:alt" content="<?= htmlspecialchars($post['image_alt'] ?? $post['title']) ?>">
  <meta property="og:site_name" content="<?= htmlspecialchars($settings['site_name']) ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($post['title']) ?>">
  <meta name="twitter:description"
    content="<?= htmlspecialchars($post['summary'] ?? substr(strip_tags($content), 0, 150)) ?>">
  <?php if (!empty($post['featured_image'])): ?>
    <meta name="twitter:image" content="<?= rtrim($settings['site_url'], '/') . $post['featured_image'] ?>">
  <?php endif; ?>
  <meta name="twitter:image:alt" content="<?= htmlspecialchars($post['image_alt'] ?? $post['title']) ?>">

  <link rel="canonical" href="<?= rtrim($settings['site_url'], '/') . '/' . $post['slug'] ?>">
  <link href="/style.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet"
    type="text/css" />
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800"
    rel="stylesheet" type="text/css" />
</head>

<body>
  <!-- Navigation -->
  <nav class="site-nav">
    <div class="container">
      <a href="/index.html" class="site-brand"><?= htmlspecialchars($settings['site_name']) ?></a>
      <button class="menu-toggle" aria-label="Toggle Navigation">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
      </button>
      <div class="nav-links">
        <?php foreach ($menus as $menu): ?>
          <a href="/<?= htmlspecialchars($menu['url']) ?>"><?= htmlspecialchars($menu['label']) ?></a>
        <?php endforeach; ?>
        <a href="/search.html" style="margin-left: 15px;">🔍</a>
      </div>
    </div>
  </nav>

  <script>
    document.querySelector('.menu-toggle').addEventListener('click', function () {
      document.querySelector('.nav-links').classList.toggle('active');
    });

    // Dark Mode Logic
    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'theme-toggle';
    toggleBtn.innerHTML = '<span class="icon-moon">🌙</span><span class="icon-sun">☀️</span>';
    toggleBtn.setAttribute('aria-label', 'Toggle Dark Mode');

    // Insert into nav-links
    const navLinks = document.querySelector('.nav-links');
    navLinks.appendChild(toggleBtn);

    // Init Theme
    if (localStorage.getItem('theme') === 'dark') {
      document.body.classList.add('dark');
    }

    // Toggle Theme
    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark');
      localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
    });
  </script>

  <!-- Page Header -->
  <?php
  $bgImage = !empty($post['featured_image']) ? $post['featured_image'] : '';
  $headerStyle = !empty($bgImage)
    ? "background-image: url('$bgImage');"
    : "background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);";
  ?>
  <header class="site-header" role="img" aria-label="<?= htmlspecialchars($post['title']) ?>"
    style="<?= $headerStyle ?>">
    <div class="overlay"></div>
    <div class="container">
      <div class="site-heading">
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <span class="meta">Posted on <?= date('d M Y, H:i', strtotime($post['published_at'])) ?>
          <?php if (!empty($post['category'])): ?>
            in
            <a href="/category/<?= slugify($post['category']) ?>"
              style="color: white; text-decoration: underline;"><?= htmlspecialchars($post['category']) ?></a>
          <?php endif; ?>
        </span>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <article class="container content-container">
    <?= $content ?>

    <div class="mt-8 mb-8" style="margin-top: 2rem; margin-bottom: 2rem;">
      <?php if (!empty($post['tags'])): ?>
        <div class="tags">
          <?php foreach ($post['tags'] as $tag):
            $tag = trim($tag); ?>
            <a href="/tag/<?= slugify($tag) ?>" class="tag"
              style="background-color: #0085a1; color: white; text-decoration: none; margin-right: 5px; margin-bottom: 5px;">#<?= htmlspecialchars($tag) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="share-container" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
      <h3 style="margin-bottom: 1.5rem; font-size: 1.2rem; font-weight: 600; color: #333;">Bagikan postingan ini:</h3>
      <div style="display: flex; gap: 1rem;">
        <?php
        $shareUrl = rtrim($settings['site_url'], '/') . '/' . $post['slug'];
        $shareTitle = $post['title'];
        ?>
        <!-- Twitter / X -->
        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($shareTitle) ?>&url=<?= urlencode($shareUrl) ?>"
          target="_blank" rel="noopener noreferrer"
          style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #000; color: white; text-decoration: none; transition: transform 0.2s;">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4l11.733 16h4.267l-11.733 -16z" />
            <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" />
          </svg>
        </a>

        <!-- Facebook -->
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>" target="_blank"
          rel="noopener noreferrer"
          style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #1877f2; color: white; text-decoration: none; transition: transform 0.2s;">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
          </svg>
        </a>

        <!-- WhatsApp -->
        <a href="https://api.whatsapp.com/send?text=<?= urlencode($shareTitle . ' ' . $shareUrl) ?>" target="_blank"
          rel="noopener noreferrer"
          style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #25d366; color: white; text-decoration: none; transition: transform 0.2s;">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"
            stroke-linecap="round" stroke-linejoin="round">
            <path
              d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
            </path>
          </svg>
        </a>
      </div>
    </div>
  </article>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="copyright"><?= $settings['footer_text'] ?></div>
    </div>
  </footer>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "<?= addslashes($post['title']) ?>",
    "image": [
      "<?= !empty($post['featured_image']) ? rtrim($settings['site_url'], '/') . $post['featured_image'] : '' ?>"
    ],
    "datePublished": "<?= date('Y-m-d', strtotime($post['published_at'])) ?>",
    "dateModified": "<?= date('Y-m-d', strtotime($post['published_at'])) ?>",
    "author": {
      "@type": "Organization",
      "name": "<?= addslashes($settings['site_name']) ?>"
    },
    "publisher": {
      "@type": "Organization",
      "name": "<?= addslashes($settings['site_name']) ?>",
      "logo": {
        "@type": "ImageObject",
        "url": "<?= rtrim($settings['site_url'], '/') ?>/favicon.ico"
      }
    },
    "description": "<?= addslashes($post['summary'] ?? substr(strip_tags($content), 0, 150)) ?>"
  }
  </script>
</body>

</html>