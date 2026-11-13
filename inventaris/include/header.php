<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login, lempar ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Inventaris Barang</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <nav class="bg-indigo-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="text-xl font-bold tracking-wide">📦 GudangKu</a>
            <div class="flex items-center gap-3">
                <a href="transaksi.php" class="bg-indigo-500 hover:bg-indigo-400 px-4 py-2 rounded-lg text-sm font-medium transition">📊 Transaksi</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="tambah.php" class="bg-indigo-500 hover:bg-indigo-400 px-4 py-2 rounded-lg text-sm font-medium transition">+ Tambah Barang</a>
                <?php endif; ?>
                <div class="flex items-center gap-2 bg-indigo-700 px-4 py-2 rounded-lg">
                    <span class="text-sm">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                    <span class="text-xs bg-indigo-500 px-2 py-0.5 rounded"><?= htmlspecialchars($_SESSION['role'] ?? 'guest') ?></span>
                </div>
                <a href="logout.php" class="bg-rose-500 hover:bg-rose-400 px-4 py-2 rounded-lg text-sm font-medium transition">🚪 Logout</a>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 py-8">
