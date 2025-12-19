<?php
require 'app/Models/PostModel.php';

// Set Timezone to Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

$model = new PostModel();
$posts = $model->all();

// Helper: Slugify
function slugify($text)
{
  // Replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);
  // Transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
  // Remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);
  // Trim
  $text = trim($text, '-');
  // Remove duplicate -
  $text = preg_replace('~-+~', '-', $text);
  // Lowercase
  $text = strtolower($text);
  if (empty($text)) {
    return 'n-a';
  }
  return $text;
}

// Sort posts by published_at descending (Newest First)
usort($posts, function ($a, $b) {
  return strtotime($b['published_at']) - strtotime($a['published_at']);
});


// Collect posts by tag
$postsByTag = [];
$postsByCategory = [];

// Read menus
$menusFile = __DIR__ . '/storage/menus.json';
$menus = json_decode(file_get_contents($menusFile), true) ?? [];

// Read settings
$settingsFile = __DIR__ . '/storage/settings.json';
$settings = json_decode(file_get_contents($settingsFile), true) ?? [
  'site_name' => 'Start Bootstrap',
  'footer_text' => 'Copyright &copy; Your Website 2025'
];

foreach ($posts as $post) {
  ob_start();
  include 'app/Views/static/post.php';
  $content = ob_get_clean();

  $title = $post['title'];
  ob_start();
  include 'app/Views/static/layout.php';
  $html = ob_get_clean();

  file_put_contents('public/static/posts/' . $post['slug'] . '.html', $html);

  // Collect tags
  foreach ($post['tags'] ?? [] as $tag) {
    $tag = trim($tag);
    if (!empty($tag)) {
      $postsByTag[slugify($tag)][] = $post; // Key is now slugified
    }
  }

  // Collect category
  $category = trim($post['category'] ?? 'Uncategorized');
  $postsByCategory[slugify($category)][] = $post; // Key is now slugified
}

echo "Posts generated\n";

// Generate Search JSON
$searchData = array_map(function ($post) {
  return [
    'title' => $post['title'],
    'slug' => $post['slug'],
    'tags' => $post['tags'] ?? [],
    'category' => $post['category'] ?? 'Uncategorized',
    'summary' => substr(strip_tags($post['content']), 0, 150) . '...',
    'published_at' => $post['published_at']
  ];
}, $posts);

file_put_contents('public/search.json', json_encode($searchData, JSON_PRETTY_PRINT));
echo "search.json generated\n";


// Generate Tag Pages
if (!is_dir('public/static/tags')) {
  mkdir('public/static/tags', 0777, true);
}

// Read menus
$menusFile = __DIR__ . '/storage/menus.json';
$menus = json_decode(file_get_contents($menusFile), true) ?? [];

// Read settings (re-read if needed or just use global if scope allows, but re-reading is safer for copy-paste blocks or functions later)
$settingsFile = __DIR__ . '/storage/settings.json';
$settings = json_decode(file_get_contents($settingsFile), true) ?? [
  'site_name' => 'Start Bootstrap',
  'footer_text' => 'Copyright &copy; Your Website 2025'
];

// --- GENERATE TAG PAGES ---
foreach ($postsByTag as $tagSlug => $tagPosts) {
  // We need the original tag name for display, but array key is slug.
  // We'll take the first post's matching tag as the display name.
  // Find the original tag string that matches this slug
  $displayTag = $tagSlug;
  foreach ($tagPosts[0]['tags'] as $t) {
    if (slugify($t) === $tagSlug) {
      $displayTag = $t;
      break;
    }
  }

  ob_start();
  ?>
  <!DOCTYPE html>
  <html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
      content="Arsip artikel dengan tag #<?= htmlspecialchars($displayTag) ?>. Temukan postingan terkait di sini.">
    <title>Tag: <?= htmlspecialchars($displayTag) ?></title>
    <link rel="canonical" href="<?= rtrim($settings['site_url'], '/') . '/tag/' . $tagSlug ?>">
    <link href="/style.css?v=<?= time() ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet"
      type="text/css" />
    <link
      href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800"
      rel="stylesheet" type="text/css" />
  </head>
  <!-- ... existing body ... -->

  <body>
    <?php /* Existing Navbar Code */ ?>
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


    </script>
    <header class="site-header" role="img" aria-label="Tag Header Image"
      style="background: linear-gradient(135deg, #0085a1 0%, #004d61 100%);">
      <div class="overlay"></div>
      <div class="container">
        <div class="site-heading">
          <h1>#<?= htmlspecialchars($displayTag) ?></h1>
          <span class="subheading">Postingan dengan tag ini</span>
        </div>
      </div>
    </header>
    <div class="container content-container">
      <?php foreach ($tagPosts as $post): ?>
        <div class="post-preview">
          <a href="/<?= $post['slug'] ?>">
            <h2 class="post-title"><?= htmlspecialchars($post['title']) ?></h2>
            <h3 class="post-subtitle"><?= htmlspecialchars(substr(strip_tags($post['content']), 0, 100)) ?>...</h3>
          </a>
          <p class="post-meta">
            Posted on <?= $post['published_at'] ?>
            <?php if (!empty($post['tags'])): ?>
              &middot; tags:
              <?php foreach ($post['tags'] as $t):
                $t = trim($t); ?>
                <a href="/tag/<?= slugify($t) ?>" class="tag"
                  style="background-color: #0085a1; color: white; text-decoration: none;">#<?= htmlspecialchars($t) ?></a>
              <?php endforeach; ?>
            <?php endif; ?>
          </p>
        </div>
        <hr class="divider" />
      <?php endforeach; ?>
    </div>
    <footer class="site-footer">
      <div class="container">
        <div class="copyright">Copyright &copy; Your Website 2025</div>
      </div>
    </footer>
  </body>

  </html>
  <?php
  $html = ob_get_clean();
  file_put_contents('public/static/tags/' . $tagSlug . '.html', $html);
}
echo "Tag pages generated\n";

// --- GENERATE CATEGORY PAGES ---
if (!is_dir('public/static/categories')) {
  mkdir('public/static/categories', 0777, true);
}
foreach ($postsByCategory as $categorySlug => $categoryPosts) {
  // Original category name derived from first post (since we used slug as key)
  $displayCategory = $categoryPosts[0]['category'] ?? 'Uncategorized';

  ob_start();
  ?>
  <!DOCTYPE html>
  <html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
      content="Kategori Artikel: <?= htmlspecialchars($displayCategory) ?>. Baca kumpulan tulisan menarik seputar <?= htmlspecialchars($displayCategory) ?>.">
    <title>Kategori: <?= htmlspecialchars($displayCategory) ?></title>
    <link rel="canonical" href="<?= rtrim($settings['site_url'], '/') . '/category/' . $categorySlug ?>">
    <link href="/style.css?v=<?= time() ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet"
      type="text/css" />
    <link
      href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800"
      rel="stylesheet" type="text/css" />
  </head>

  <body>
    <!-- Navbar -->
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
    <header class="site-header" role="img" aria-label="Category Header Image"
      style="background: linear-gradient(135deg, #667db6 0%, #0082c8 100%, #0082c8 100%, #667db6 100%);">
      <div class="overlay"></div>
      <div class="container">
        <div class="site-heading">
          <h1><?= htmlspecialchars($displayCategory) ?></h1>
          <span class="subheading">Kategori</span>
        </div>
      </div>
    </header>
    <div class="container content-container">
      <?php foreach ($categoryPosts as $post): ?>
        <div class="post-preview">
          <a href="/<?= $post['slug'] ?>">
            <h2 class="post-title"><?= htmlspecialchars($post['title']) ?></h2>
            <h3 class="post-subtitle"><?= htmlspecialchars(substr(strip_tags($post['content']), 0, 100)) ?>...</h3>
          </a>
          <p class="post-meta">
            Posted on <?= date('d M Y, H:i', strtotime($post['published_at'])) ?>
            in <strong><?= htmlspecialchars($post['category'] ?? 'Uncategorized') ?></strong>
            <?php if (!empty($post['tags'])): ?>
              &middot; tags:
              <?php foreach ($post['tags'] as $t):
                $t = trim($t); ?>
                <a href="/tag/<?= slugify($t) ?>" class="tag"
                  style="background-color: #0085a1; color: white; text-decoration: none;">#<?= htmlspecialchars($t) ?></a>
              <?php endforeach; ?>
            <?php endif; ?>
          </p>
        </div>
        <hr class="divider" />
      <?php endforeach; ?>
    </div>
    <footer class="site-footer">
      <div class="container">
        <div class="copyright"><?= $settings['footer_text'] ?></div>
      </div>
    </footer>
  </body>

  </html>
  <?php
  $html = ob_get_clean();
  file_put_contents('public/static/categories/' . $categorySlug . '.html', $html);
}
echo "Category pages generated\n";

// Generate Homepage and Pagination
$postsPerPage = 5;
$pages = array_chunk($posts, $postsPerPage);
$totalPages = count($pages);

if ($totalPages === 0) {
  // Handle case with no posts
  $pages = [[]]; // Create one empty page
  $totalPages = 1;
}

foreach ($pages as $pageIndex => $pagePosts) {
  $pageNum = $pageIndex + 1;
  $fileName = ($pageNum === 1) ? 'public/index.html' : "public/page{$pageNum}.html";

  ob_start();
  ?>
  <!DOCTYPE html>
  <html lang="id">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
      content="<?= htmlspecialchars($settings['site_name']) ?> - Blog tentang teknologi, koding, dan tutorial terbaru. Halaman <?= $pageNum ?>.">
    <title>Blog Saya<?= $pageNum > 1 ? " - Halaman $pageNum" : "" ?></title>
    <link rel="canonical"
      href="<?= rtrim($settings['site_url'], '/') . ($pageNum > 1 ? '/page' . $pageNum . '.html' : '/') ?>">
    <link href="/style.css?v=<?= time() ?>" rel="stylesheet">
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


    </script>

    <!-- Page Header -->
    <header class="site-header" role="img" aria-label="Site Header Image"
      style="background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);">
      <div class="overlay"></div>
      <div class="container">
        <div class="site-heading">
          <h1><?= htmlspecialchars($settings['site_name']) ?></h1>
          <span class="subheading">A Blog Theme by Start Bootstrap</span>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="container content-container">
      <?php foreach ($pagePosts as $post): ?>
        <div class="post-preview">
          <a href="/<?= $post['slug'] ?>">
            <h2 class="post-title">
              <?= htmlspecialchars($post['title']) ?>
            </h2>
            <h3 class="post-subtitle">
              <?= htmlspecialchars(substr(strip_tags($post['content']), 0, 100)) ?>...
            </h3>
          </a>
          <p class="post-meta">
            Posted on <?= $post['published_at'] ?>
            <?php if (!empty($post['tags'])): ?>
              &middot; tags:
              <?php foreach ($post['tags'] as $tag):
                $tag = trim($tag); ?>
                <a href="/tag/<?= slugify($tag) ?>" class="tag"
                  style="background-color: #0085a1; color: white; text-decoration: none;">#<?= htmlspecialchars($tag) ?></a>
              <?php endforeach; ?>
            <?php endif; ?>
          </p>
        </div>
        <hr class="divider" />
      <?php endforeach; ?>

      <!-- Pager -->
      <div class="pager" style="display: flex; justify-content: space-between;">
        <div>
          <?php if ($pageNum > 1): ?>
            <?php $prevPage = ($pageNum - 1 === 1) ? 'index.html' : "page" . ($pageNum - 1) . ".html"; ?>
            <a href="<?= $prevPage ?>" class="btn">&larr; Newer Posts</a>
          <?php endif; ?>
        </div>
        <div>
          <?php if ($pageNum < $totalPages): ?>
            <a href="page<?= $pageNum + 1 ?>.html" class="btn">Older Posts &rarr;</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
      <div class="container">
        <div class="copyright"><?= $settings['footer_text'] ?></div>
      </div>
    </footer>
  </body>

  </html>
  <?php
  $html = ob_get_clean();
  file_put_contents($fileName, $html);
}
echo "Homepage and pagination generated ($totalPages pages)\n";

// Generate Search Page
ob_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description"
    content="Pencarian Artikel di <?= htmlspecialchars($settings['site_name']) ?>. Temukan topik yang Anda sukai.">
  <title>Pencarian - <?= htmlspecialchars($settings['site_name']) ?></title>
  <link rel="canonical" href="<?= rtrim($settings['site_url'], '/') . '/search.html' ?>">
  <link href="style.css?v=<?= time() ?>" rel="stylesheet">

  <link href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet"
    type="text/css" />
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800"
    rel="stylesheet" type="text/css" />
  <link rel="shortcut icon" href="/favicon.ico">
  <style>
    .search-input {
      width: 100%;
      padding: 1rem;
      font-size: 1.2rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-bottom: 2rem;
    }
  </style>
</head>

<body>
  <!-- Navigation -->
  <nav class="site-nav">
    <div class="container">
      <a href="index.html" class="site-brand"><?= htmlspecialchars($settings['site_name']) ?></a>
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
  </script>

  <!-- Page Header -->
  <header class="site-header" role="img" aria-label="Books Library Background"
    style="background-image: url('https://source.unsplash.com/random/1600x900/?books,library');">
    <div class="overlay"></div>
    <div class="container">
      <div class="site-heading">
        <h1>Pencarian</h1>
        <span class="subheading">Temukan artikel yang Anda cari</span>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="container content-container">
    <input type="text" id="searchInput" class="search-input" placeholder="Ketik kata kunci...">
    <div id="searchResults"></div>
  </div>

  <script>
    let posts = [];

    fetch('search.json')
      .then(response => response.json())
      .then(data => {
        posts = data;
      });

    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('searchResults');

    searchInput.addEventListener('input', (e) => {
      const term = e.target.value.toLowerCase();
      resultsContainer.innerHTML = '';

      if (term.length < 2) return;

      const filtered = posts.filter(post =>
        post.title.toLowerCase().includes(term) ||
        post.summary.toLowerCase().includes(term) ||
        (post.tags && post.tags.some(tag => tag.toLowerCase().includes(term)))
      );

      if (filtered.length === 0) {
        resultsContainer.innerHTML = '<p>Tidak ditemukan hasil.</p>';
        return;
      }

      filtered.forEach(post => {
        const item = document.createElement('div');
        item.className = 'post-preview';
        item.innerHTML = `
          <a href="/${post.slug}">
            <h2 class="post-title">${post.title}</h2>
            <h3 class="post-subtitle">${post.summary}</h3>
          </a>
          <p class="post-meta">Posted on ${new Date(post.published_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
          <hr class="divider">
        `;
        resultsContainer.appendChild(item);
      });
    });
  </script>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="copyright">
        <?= $settings['footer_text'] ?>
      </div>
    </div>
  </footer>
</body>

</html>
<?php
$html = ob_get_clean();
file_put_contents('public/search.html', $html);
echo "Search page generated\n";

// Generate Sitemap XML
$baseUrl = rtrim($settings['site_url'] ?? 'https://example.com', '/');
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Homepage
$xml .= "  <url>\n";
$xml .= "    <loc>" . htmlspecialchars($baseUrl . '/') . "</loc>\n";
$xml .= "    <changefreq>daily</changefreq>\n";
$xml .= "    <priority>1.0</priority>\n";
$xml .= "  </url>\n";

// Posts
foreach ($posts as $post) {
  if (empty($post['slug']))
    continue;
  $date = date('c', strtotime($post['published_at'])); // W3C Datetime format
  $url = $baseUrl . '/' . $post['slug'];

  $xml .= "  <url>\n";
  $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
  $xml .= "    <lastmod>" . $date . "</lastmod>\n";
  $xml .= "    <changefreq>monthly</changefreq>\n";
  $xml .= "    <priority>0.8</priority>\n";
  $xml .= "  </url>\n";
}

// Pages (Tags)
foreach ($postsByTag as $tagSlug => $p) {
  $url = $baseUrl . '/tag/' . $tagSlug; // Clean URL

  $xml .= "  <url>\n";
  $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
  $xml .= "    <changefreq>weekly</changefreq>\n";
  $xml .= "    <priority>0.5</priority>\n";
  $xml .= "  </url>\n";
}

// Categories
foreach ($postsByCategory as $categorySlug => $p) {
  $url = $baseUrl . '/category/' . $categorySlug; // Clean URL

  $xml .= "  <url>\n";
  $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
  $xml .= "    <changefreq>weekly</changefreq>\n";
  $xml .= "    <priority>0.6</priority>\n";
  $xml .= "  </url>\n";
}

$xml .= '</urlset>';

file_put_contents('public/sitemap.xml', $xml);
echo "sitemap.xml generated (Standards Compliant)\n";

// Generate robots.txt
$robotsTxt = "User-agent: *\n";
$robotsTxt .= "Allow: /\n";
$robotsTxt .= "Sitemap: " . $baseUrl . "/sitemap.xml\n";
file_put_contents('public/robots.txt', $robotsTxt);
echo "robots.txt generated\n";
