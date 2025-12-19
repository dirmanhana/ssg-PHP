<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $users = json_decode(file_get_contents('../storage/users.json'), true) ?? [];

    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-200 h-screen flex items-center justify-center">

    <div class="w-full max-w-xs">
        <form method="post" action="login.php" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4 text-center">
                <h1 class="text-2xl font-bold text-gray-700">Login Admin</h1>
                <p class="text-gray-500 text-sm">Masuk untuk mengelola blog.</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= $error ?></span>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    Username
                </label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="username" name="username" type="text" placeholder="Username" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Password
                </label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                    id="password" name="password" type="password" placeholder="******************" required>
            </div>

            <div class="flex items-center justify-between">
                <button
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full transition duration-150"
                    type="submit">
                    Masuk
                </button>
            </div>
        </form>
        <p class="text-center text-gray-500 text-xs">
            &copy;2025 SSG Admin. All rights reserved.
        </p>
    </div>

</body>

</html>