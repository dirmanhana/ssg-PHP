<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Dashboard' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

        <!-- Sidebar -->
        <aside
            class="w-64 bg-gray-800 text-white flex-shrink-0 hidden md:flex flex-col transform md:translate-x-0 transition-transform duration-200 ease-in-out z-20"
            :class="{'translate-x-0 fixed inset-y-0 left-0': sidebarOpen, '-translate-x-full fixed inset-y-0 left-0': !sidebarOpen, 'md:relative md:translate-x-0': true}">

            <div class="h-16 flex items-center justify-center border-b border-gray-700">
                <span class="text-2xl font-bold text-blue-400">Panel Admin</span>
            </div>

            <nav class="flex-1 overflow-y-auto py-4">
                <ul>
                    <li>
                        <a href="index.php"
                            class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-gray-700' : '' ?>">
                            Dashboard & Posts
                        </a>
                    </li>
                    <li>
                        <a href="editor.php"
                            class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'editor.php' ? 'bg-gray-700' : '' ?>">
                            Tulis Post Baru
                        </a>
                    </li>
                    <li>
                        <a href="settings.php"
                            class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'bg-gray-700' : '' ?>">
                            Pengaturan Situs
                        </a>
                    </li>
                    <li>
                        <a href="menus.php"
                            class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white <?= basename($_SERVER['PHP_SELF']) == 'menus.php' ? 'bg-gray-700' : '' ?>">
                            Kelola Menu
                        </a>
                    </li>
                    <li>
                        <a href="../" target="_blank"
                            class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 hover:text-white">
                            Lihat Website ↗
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="p-4 border-t border-gray-700">
                <a href="index.php?logout=1"
                    class="block w-full text-center py-2 px-4 bg-red-600 hover:bg-red-700 text-white rounded transition duration-200">
                    Logout
                </a>
            </div>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
            class="fixed inset-0 bg-black bg-opacity-50 z-10 md:hidden"></div>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden relative w-full">

            <!-- Top Header -->
            <header class="flex justify-between items-center py-4 px-6 bg-white border-b-4 border-blue-500 shadow-md">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none md:hidden p-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800 ml-2 md:ml-0"><?= $pageTitle ?? 'Dashboard' ?></h2>
                </div>

                <div class="flex items-center">
                    <div class="text-sm text-gray-600">
                        Login sebagai <span
                            class="font-bold text-gray-800"><?= $_SESSION['username'] ?? 'Admin' ?></span>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">