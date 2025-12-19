<?php
require 'auth.php'; // Protect this page

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$postsFile = '../storage/posts.json';
$posts = json_decode(file_get_contents($postsFile), true) ?? [];

// Sort posts by published_at descending (Newest First)
usort($posts, function ($a, $b) {
    return strtotime($b['published_at']) - strtotime($a['published_at']);
});

// --- BULK DELETE LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'], $_POST['ids'])) {
    $idsToDelete = $_POST['ids'];
    $originalCount = count($posts);

    // Filter out deleted posts
    $posts = array_filter($posts, function ($post) use ($idsToDelete) {
        return !in_array($post['id'], $idsToDelete);
    });

    if (count($posts) < $originalCount) {
        // Re-index array
        $posts = array_values($posts);
        file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT));

        // Rebuild site
        chdir(__DIR__ . '/..');
        exec('php build.php');

        $message = count($idsToDelete) . " postingan berhasil dihapus.";
    }
}

// --- SEARCH LOGIC ---
$searchQuery = $_GET['q'] ?? '';
if (!empty($searchQuery)) {
    $posts = array_filter($posts, function ($post) use ($searchQuery) {
        return stripos($post['title'], $searchQuery) !== false ||
            stripos($post['category'] ?? '', $searchQuery) !== false;
    });
}

// --- PAGINATION LOGIC ---
$limit = 10; // Posts per page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$totalPosts = count($posts);
$totalPages = ceil($totalPosts / $limit);
$offset = ($page - 1) * $limit;
$currentPosts = array_slice($posts, $offset, $limit);

// Set Timezone to Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

$pageTitle = 'Dashboard';
require 'layout/header.php';
?>

<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <div>
        <h3 class="text-gray-700 text-3xl font-medium">Daftar Postingan</h3>
        <p class="text-gray-500 text-sm">Total: <?= $totalPosts ?> artikel</p>
    </div>

    <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
        <!-- Search Form -->
        <form method="get" class="flex w-full md:w-auto">
            <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Cari judul..."
                class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-r">
                🔍
            </button>
        </form>

        <a href="editor.php"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center justify-center whitespace-nowrap">
            <span class="mr-2">➕</span> Buat Baru
        </a>
    </div>
</div>

<?php if (isset($message)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?= $message ?></span>
    </div>
<?php endif; ?>

<form method="post" id="bulkForm">
    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <table class="min-w-full w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-center w-10">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                    </th>
                    <th class="py-3 px-6 text-left">Judul</th>
                    <th class="py-3 px-6 text-center">Kategori</th>
                    <th class="py-3 px-6 text-center">Tanggal</th>
                    <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <?php foreach ($currentPosts as $post): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-center">
                            <input type="checkbox" name="ids[]" value="<?= $post['id'] ?>" class="post-checkbox">
                        </td>
                        <td class="py-3 px-6 text-left">
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($post['title']) ?></span>
                            <?php if (!empty($post['image_alt'])): ?>
                                <span class="text-xs text-gray-400 ml-2" title="Alt Text ada">🖼️</span>
                            <?php endif; ?>
                            <div class="text-xs text-gray-500 mt-1"><?= $post['slug'] ?></div>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <span class="bg-blue-200 text-blue-600 py-1 px-3 rounded-full text-xs">
                                <?= htmlspecialchars($post['category'] ?? '-') ?>
                            </span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex flex-col">
                                <span><?= date('d M Y', strtotime($post['published_at'])) ?></span>
                                <span
                                    class="text-xs text-gray-400"><?= date('H:i', strtotime($post['published_at'])) ?></span>
                            </div>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <a href="../<?= $post['slug'] ?>" target="_blank"
                                    class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110" title="Lihat">
                                    👁️
                                </a>
                                <a href="editor.php?id=<?= $post['id'] ?>"
                                    class="w-4 mr-2 transform hover:text-blue-500 hover:scale-110" title="Edit">
                                    ✏️
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($currentPosts)): ?>
                    <tr>
                        <td colspan="5" class="py-3 px-6 text-center text-gray-500">
                            <?= !empty($searchQuery) ? 'Tidak ada hasil untuk pencarian ini.' : 'Belum ada postingan.' ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bulk Actions & Pagination -->
    <div class="flex flex-col md:flex-row justify-between items-center mt-4 mb-8">
        <!-- Bulk Delete Button -->
        <div>
            <button type="submit" name="bulk_delete" onclick="return confirm('Yakin hapus postingan yang dipilih?')"
                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm focus:outline-none focus:shadow-outline"
                style="display: none;" id="bulkDeleteBtn">
                Hapus Terpilih 🗑️
            </button>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex bg-white rounded-lg shadow text-sm mt-4 md:mt-0">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&q=<?= urlencode($searchQuery) ?>"
                        class="px-3 py-2 border-r border-gray-200 hover:bg-gray-100 text-gray-600">
                        &larr; Prev
                    </a>
                <?php endif; ?>

                <div class="px-3 py-2 text-gray-500 border-r border-gray-200">
                    Halaman <?= $page ?> dari <?= $totalPages ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&q=<?= urlencode($searchQuery) ?>"
                        class="px-3 py-2 hover:bg-gray-100 text-gray-600">
                        Next &rarr;
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</form>

<script>
    function toggleSelectAll() {
        const mainCheckbox = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.post-checkbox');
        checkboxes.forEach(cb => cb.checked = mainCheckbox.checked);
        toggleDeleteBtn();
    }

    function toggleDeleteBtn() {
        const checkboxes = document.querySelectorAll('.post-checkbox:checked');
        const btn = document.getElementById('bulkDeleteBtn');
        if (checkboxes.length > 0) {
            btn.style.display = 'block';
        } else {
            btn.style.display = 'none';
        }
    }

    // Add event listener to each checkbox
    document.querySelectorAll('.post-checkbox').forEach(cb => {
        cb.addEventListener('change', toggleDeleteBtn);
    });
</script>

<?php require 'layout/footer.php'; ?>