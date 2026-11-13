<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek role admin untuk semua operasi
if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak! Halaman ini khusus Administrator.");
}

require_once 'inventaris/config/database.php';

$action = $_GET['action'] ?? '';

if ($action === 'add') {
    // Sanitasi & validasi input
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori    = trim($_POST['kategori'] ?? '');
    $stok        = (int)($_POST['stok'] ?? 0);
    $harga       = (float)($_POST['harga'] ?? 0);

    if (empty($nama_barang) || empty($kategori) || $stok < 0 || $harga < 0) {
        header("Location: tambah.php");
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO barang (nama_barang, kategori, stok, harga) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nama_barang, $kategori, $stok, $harga]);

    header("Location: index.php");
    exit;

} elseif ($action === 'edit') {
    // Sanitasi & validasi input
    $id          = (int)($_POST['id'] ?? 0);
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori    = trim($_POST['kategori'] ?? '');
    $stok        = (int)($_POST['stok'] ?? 0);
    $harga       = (float)($_POST['harga'] ?? 0);

    if (!$id || empty($nama_barang) || empty($kategori) || $stok < 0 || $harga < 0) {
        header("Location: index.php");
        exit;
    }

    $stmt = $pdo->prepare("UPDATE barang SET nama_barang = ?, kategori = ?, stok = ?, harga = ? WHERE id = ?");
    $stmt->execute([$nama_barang, $kategori, $stok, $harga, $id]);

    header("Location: index.php");
    exit;

} elseif ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM barang WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: index.php");
    exit;

} else {
    header("Location: index.php");
    exit;
}
?>
