<?php
require 'auth.php';
$file = __DIR__ . '/../storage/settings.json';
$settings = json_decode(file_get_contents($file), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['update_site_settings'])) {
        $settings['site_name'] = $_POST['site_name'];
        $settings['site_url'] = rtrim($_POST['site_url'], '/');
        $settings['footer_text'] = $_POST['footer_text'];

        // Handle Favicon Upload
        if (isset($_FILES['favicon_upload']) && $_FILES['favicon_upload']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/';
            $targetFile = $uploadDir . 'favicon.ico';

            // Allow basic image types
            $allowedExts = ['ico', 'png', 'jpg', 'jpeg'];
            $fileExt = strtolower(pathinfo($_FILES['favicon_upload']['name'], PATHINFO_EXTENSION));

            if (in_array($fileExt, $allowedExts)) {
                move_uploaded_file($_FILES['favicon_upload']['tmp_name'], $targetFile);
            }
        }

        file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT));

        // Rebuild static site
        chdir(__DIR__ . '/..');
        exec('php build.php');

        $message = "Pengaturan situs berhasil disimpan!";
    }

    if (isset($_POST['update_account'])) {
        $usersFile = __DIR__ . '/../storage/users.json';
        $users = json_decode(file_get_contents($usersFile), true) ?? [];

        $currentUsername = $_SESSION['username'] ?? 'admin';
        // Fallback for key check if session empty (legacy login)
        if (!isset($users[$currentUsername])) {
            $currentUsername = key($users); // Get first user
        }

        $oldPassword = $_POST['old_password'];
        $newUsername = trim($_POST['new_username']);
        $newPassword = $_POST['new_password'];

        if (password_verify($oldPassword, $users[$currentUsername])) {
            // Remove old user entry
            unset($users[$currentUsername]);

            // Set new user entry
            $finalUsername = !empty($newUsername) ? $newUsername : $currentUsername;
            $finalPasswordHash = !empty($newPassword) ? password_hash($newPassword, PASSWORD_DEFAULT) : password_hash($oldPassword, PASSWORD_DEFAULT); // Note: reusing old password hash if not changed is safer but here we might just rehash or keep. 
            // Better: if new password empty, keep old hash.
            if (!empty($newPassword)) {
                $finalPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            } else {
                // If we are changing username but keeping password, we can't reuse the HASH if we don't have it in $post? 
                // Wait, we verified it, so we can re-use the stored hash from $users var before unset?
                // Actually I unset it too early.
                // Let's refetch.
                $users = json_decode(file_get_contents($usersFile), true) ?? [];
                $finalPasswordHash = $users[$currentUsername];
                unset($users[$currentUsername]);

                if (!empty($newPassword)) {
                    $finalPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                }
            }

            $users[$finalUsername] = $finalPasswordHash;
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

            $_SESSION['username'] = $finalUsername;
            $message = "Akun berhasil diperbarui!";

        } else {
            $error = "Password lama salah!";
        }
    }
    if (isset($_POST['rebuild_site'])) {
        chdir(__DIR__ . '/..');
        exec('php build.php');
        $message = "Situs statis dan Sitemap.xml berhasil digenerasi ulang!";
    }
}
?>
<!DOCTYPE html>
<?php
$pageTitle = 'Pengaturan Situs';
require 'layout/header.php';
?>

<div class="bg-white rounded shadow-lg p-4 md:p-8 max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h2 class="text-2xl font-semibold text-gray-700">Pengaturan Situs</h2>
        <span class="text-sm text-gray-500">Konfigurasi dasar</span>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= $error ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($message)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?= $message ?></span>
        </div>
    <?php endif; ?>

    <form method="post" action="settings.php" class="space-y-6" enctype="multipart/form-data">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Situs</label>
            <input type="text" name="site_name"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required>
            <p class="text-gray-500 text-xs italic mt-1">Muncul di header dan judul halaman.</p>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">URL Situs (Base URL)</label>
            <input type="url" name="site_url"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                placeholder="https://namadomain.com" value="<?= htmlspecialchars($settings['site_url'] ?? '') ?>"
                required>
            <p class="text-gray-500 text-xs italic mt-1">Tanpa garis miring di akhir. Digunakan untuk Sitemap dan
                Canonical URL.</p>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Upload Favicon</label>
            <input type="file" name="favicon_upload" accept=".ico,.png,.jpg,.jpeg"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <p class="text-gray-500 text-xs italic mt-1">Upload gambar (ico/png) untuk ikon browser. Akan disimpan
                sebagai favicon.ico</p>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Teks Footer</label>
            <input type="text" name="footer_text"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                value="<?= htmlspecialchars($settings['footer_text'] ?? '') ?>">
            <p class="text-gray-500 text-xs italic mt-1">Boleh menggunakan HTML entitas seperti &amp;copy;.</p>
        </div>

        <div class="flex items-center justify-end pt-4">
            <button type="submit" name="update_site_settings"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                Simpan Pengaturan Situs
            </button>
        </div>
    </form>

    <div class="border-t my-8 border-gray-300"></div>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-700">Generasi Ulang Situs</h2>
            <p class="text-gray-500 text-sm">Paksa buat ulang semua file statis HTML dan Sitemap.xml.</p>
        </div>
        <form method="post" action="settings.php">
            <button type="submit" name="rebuild_site"
                class="bg-purple-600 hover:bg-purple-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out flex items-center">
                <span class="mr-2">🔄</span> Rebuild HTML & Sitemap
            </button>
        </form>
    </div>

    <div class="border-t my-8 border-gray-300"></div>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700">Manajemen Akun</h2>
        <span class="text-sm text-gray-500">Ganti Username & Password</span>
    </div>

    <form method="post" action="settings.php" class="space-y-6">
        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Username Baru</label>
            <input type="text" name="new_username"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                placeholder="Biarkan kosong jika tidak ingin ganti"
                value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>">
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Password Baru</label>
            <input type="password" name="new_password"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                placeholder="Biarkan kosong jika tidak ingin ganti">
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Password Lama (Verifikasi)</label>
            <input type="password" name="old_password" required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                placeholder="Masukkan password saat ini">
        </div>

        <div class="flex items-center justify-end pt-4">
            <button type="submit" name="update_account"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                Update Akun
            </button>
        </div>
    </form>
</div>

<?php require 'layout/footer.php'; ?>