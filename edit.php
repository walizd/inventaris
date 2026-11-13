<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek role admin
if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak! Halaman ini khusus Administrator.");
}

require_once 'inventaris/config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM barang WHERE id = ?");
$stmt->execute([$id]);
$barang = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$barang) {
    header("Location: index.php");
    exit;
}

// header.php akan skip session_start() karena session sudah aktif
include 'inventaris/include/header.php';
?>

<div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
    <h1 class="text-xl font-bold text-gray-900 mb-6">Edit Data Barang</h1>

    <form action="proses.php?action=edit" method="POST" class="space-y-4">
        <input type="hidden" name="id" value="<?= (int)$barang['id'] ?>">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
            <input type="text" name="nama_barang" value="<?= htmlspecialchars($barang['nama_barang']) ?>" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <input type="text" name="kategori" value="<?= htmlspecialchars($barang['kategori']) ?>" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Stok</label>
            <input type="number" name="stok" min="0" value="<?= (int)$barang['stok'] ?>" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan (Rp)</label>
            <input type="number" name="harga" min="0" value="<?= (int)$barang['harga'] ?>" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div class="flex justify-end space-x-3 pt-4">
            <a href="index.php"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium">Batal</a>
            <button type="submit"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition">Perbarui</button>
        </div>
    </form>
</div>

<?php include 'inventaris/include/footer.php'; ?>
