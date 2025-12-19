<?php
require 'auth.php';
$post = [
    'id' => '',
    'title' => '',
    'content' => '',
    'tags' => []
];

if (isset($_GET['id'])) {
    $file = __DIR__ . '/../storage/posts.json';
    $posts = json_decode(file_get_contents($file), true);
    foreach ($posts as $p) {
        if ($p['id'] == $_GET['id']) {
            $post = $p;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<?php
$pageTitle = $post['id'] ? 'Edit Postingan' : 'Buat Postingan Baru';
require 'layout/header.php';
?>

<!-- Quill Styles -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-container {
        font-size: 16px;
    }

    .ql-editor {
        min-height: 300px;
        background-color: white;
    }
</style>

<div class="bg-white rounded shadow-lg p-4 md:p-8 mb-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700"><?= $post['id'] ? 'Edit Postingan' : 'Buat Postingan Baru' ?>
        </h2>
    </div>

    <form id="postForm" method="post" action="publish.php" class="space-y-6" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $post['id'] ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Judul</label>
                <input type="text" name="title"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Judul Postingan" value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Kategori</label>
                <input type="text" name="category"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Contoh: Tutorial, Berita" value="<?= htmlspecialchars($post['category'] ?? '') ?>">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Publikasi</label>
                <?php
                $published_at_val = !empty($post['published_at'])
                    ? date('Y-m-d\TH:i', strtotime($post['published_at']))
                    : date('Y-m-d\TH:i');
                ?>
                <input type="datetime-local" name="published_at"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    value="<?= $published_at_val ?>">
            </div>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Tags (pisahkan dengan koma)</label>
            <input type="text" name="tags"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                placeholder="contoh: blog, teknologi, tutorial"
                value="<?= htmlspecialchars(implode(', ', $post['tags'] ?? [])) ?>">
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Gambar Unggulan</label>

            <div class="mb-2">
                <label class="block text-gray-600 text-xs mb-1">Opsi 1: Upload Gambar (Disarankan)</label>
                <input type="file" name="featured_image_upload" accept="image/*"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mt-2">
                <label class="block text-gray-600 text-xs mb-1">Opsi 2: Gunakan URL Eksternal</label>
                <input type="text" name="featured_image"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="https://example.com/image.jpg"
                    value="<?= htmlspecialchars($post['featured_image'] ?? '') ?>">
            </div>
            <?php if (!empty($post['featured_image'])): ?>
                <div class="mt-2 text-sm text-gray-500">
                    Gambar saat ini: <a href="<?= htmlspecialchars($post['featured_image']) ?>" target="_blank"
                        class="text-blue-500 underline">Lihat</a>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Alt Text Gambar</label>
            <input type="text" name="image_alt"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                placeholder="Deskripsi gambar untuk SEO" value="<?= htmlspecialchars($post['image_alt'] ?? '') ?>">
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi Singkat (Meta Description)</label>
            <textarea name="summary"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                placeholder="Ringkasan konten untuk muncul di Google (maks. 160 karakter)"
                rows="2"><?= htmlspecialchars($post['summary'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">Konten</label>
            <div id="editor-container" class="bg-white" style="height: 400px;">
                <?= $post['content'] ?>
            </div>
            <input type="hidden" name="content" id="content">
        </div>

        <div class="flex items-center justify-end">
            <a href="index.php" class="text-gray-600 hover:text-gray-800 mr-4">Batal</a>
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?= $post['id'] ? 'Simpan Perubahan' : 'Publish Sekarang' ?>
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Tulis sesuatu yang menarik...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    document.getElementById('postForm').onsubmit = function () {
        var content = document.querySelector('input[name=content]');
        content.value = quill.root.innerHTML;
    };
</script>

<?php require 'layout/footer.php'; ?>