<?php
// session_start() cukup dipanggil sekali lewat header.php
// Tapi kita perlu cek session SEBELUM include header,
// maka kita start session di sini lalu header.php akan skip karena PHP_SESSION_ACTIVE
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

// header.php akan cek session_status() dan skip session_start() jika sudah aktif
include 'inventaris/include/header.php';
?>

<div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
    <h1 class="text-xl font-bold text-gray-900 mb-6">Tambah Barang Baru</h1>

    <form action="proses.php?action=add" method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
            <input type="text" name="nama_barang" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <input type="text" name="kategori" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Stok</label>
            <input type="number" name="stok" min="0" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Satuan (Rp)</label>
            <input type="number" name="harga" min="0" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div class="flex justify-end space-x-3 pt-4">
            <a href="index.php"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm font-medium">Batal</a>
            <button type="submit"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition">Simpan</button>
        </div>
    </form>
</div>

<?php include 'inventaris/include/footer.php'; ?>
