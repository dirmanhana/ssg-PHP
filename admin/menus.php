<?php
require 'auth.php';
$file = __DIR__ . '/../storage/menus.json';
$menus = json_decode(file_get_contents($file), true) ?? [];

$editIndex = null;
$editMenu = ['label' => '', 'url' => ''];

if (isset($_GET['edit']) && isset($menus[$_GET['edit']])) {
    $editIndex = $_GET['edit'];
    $editMenu = $menus[$editIndex];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['label'], $_POST['url'])) {
        $newItem = [
            'label' => $_POST['label'],
            'url' => $_POST['url']
        ];

        if (isset($_POST['update_index']) && is_numeric($_POST['update_index'])) {
            // Update
            $menus[$_POST['update_index']] = $newItem;
        } else {
            // Add
            $menus[] = $newItem;
        }
    } elseif (isset($_POST['delete'])) {
        // Delete
        array_splice($menus, $_POST['delete'], 1);
    }
    file_put_contents($file, json_encode($menus, JSON_PRETTY_PRINT));

    // Rebuild static site
    chdir(__DIR__ . '/..');
    exec('php build.php');

    header('Location: menus.php');
    exit;
}

$pageTitle = 'Kelola Menu';
require 'layout/header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Form Section -->
    <div class="md:col-span-1">
        <div class="bg-white rounded shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">
                <?= $editIndex !== null ? 'Edit Menu' : 'Tambah Menu Baru' ?>
            </h2>

            <form method="post" class="space-y-4">
                <?php if ($editIndex !== null): ?>
                    <input type="hidden" name="update_index" value="<?= $editIndex ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Label Menu</label>
                    <input type="text" name="label" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Contoh: About" value="<?= htmlspecialchars($editMenu['label']) ?>">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">URL Tujuan</label>
                    <input type="text" name="url" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        placeholder="Contoh: about.html" value="<?= htmlspecialchars($editMenu['url']) ?>">
                    <p class="text-xs text-gray-500 mt-1">Gunakan nama file .html atau link eksternal.</p>
                </div>

                <div class="flex justify-end pt-2">
                    <?php if ($editIndex !== null): ?>
                        <a href="menus.php"
                            class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded mr-2 focus:outline-none focus:shadow-outline text-sm">Batal</a>
                    <?php endif; ?>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out text-sm">
                        <?= $editIndex !== null ? 'Simpan Perubahan' : 'Tambah Menu' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- List Section -->
    <div class="md:col-span-2">
        <div class="bg-white rounded shadow-lg p-6 overflow-x-auto">
            <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Daftar Menu Navigasi</h2>

            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Label
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            URL
                        </th>
                        <th
                            class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $index => $menu): ?>
                        <tr>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-900 whitespace-no-wrap font-medium">
                                    <?= htmlspecialchars($menu['label']) ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                <p class="text-gray-600 whitespace-no-wrap"><?= htmlspecialchars($menu['url']) ?></p>
                            </td>
                            <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                                <div class="flex item-center justify-center">
                                    <a href="menus.php?edit=<?= $index ?>"
                                        class="text-blue-600 hover:text-blue-900 mx-2 font-semibold">Edit</a>
                                    <form method="post" class="inline-block" onsubmit="return confirm('Hapus menu ini?');">
                                        <input type="hidden" name="delete" value="<?= $index ?>">
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-900 mx-2 font-semibold bg-transparent border-0 cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($menus)): ?>
                        <tr>
                            <td colspan="3"
                                class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                                Belum ada menu. Tambahkan di formulir sebelah kiri.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
            <p class="font-bold">Info</p>
            <p class="text-sm">Urutan menu sesuai dengan daftar di atas. Menu yang ditambahkan terakhir akan muncul di
                paling kanan navigasi.</p>
        </div>
    </div>
</div>

<?php require 'layout/footer.php'; ?>