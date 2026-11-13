<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'inventaris/config/database.php';

$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM barang WHERE nama_barang LIKE ? OR kategori LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM barang ORDER BY id DESC");
}
$barangs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'inventaris/include/header.php';
?>

<div class="mb-6 flex flex-wrap justify-between items-center gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Daftar Inventaris Barang</h1>
        <span class="text-sm text-gray-500">Total: <?= count($barangs) ?> jenis barang</span>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <form action="index.php" method="GET" class="flex gap-2">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama atau kategori..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none w-64">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition shrink-0">Cari</button>
            <?php if (!empty($search)): ?>
                <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium shrink-0">Reset</a>
            <?php endif; ?>
        </form>
        <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition shrink-0">🖨️ Cetak</button>
    </div>
</div>

<div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                    <th class="py-3 px-4 font-semibold">No</th>
                    <th class="py-3 px-4 font-semibold">Nama Barang</th>
                    <th class="py-3 px-4 font-semibold">Kategori</th>
                    <th class="py-3 px-4 font-semibold">Stok</th>
                    <th class="py-3 px-4 font-semibold">Harga (Rp)</th>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <th class="py-3 px-4 font-semibold text-center aksi-col">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm">
                <?php if (empty($barangs)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-400">Tidak ada data barang yang ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($barangs as $row): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4 text-gray-500"><?= $no++ ?></td>
                        <td class="py-3 px-4 font-medium text-gray-900"><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td class="py-3 px-4">
                            <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full text-xs font-semibold"><?= htmlspecialchars($row['kategori']) ?></span>
                        </td>
                        <td class="py-3 px-4">
                            <?php if ($row['stok'] <= 5): ?>
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">
                                    ⚠️ Menipis (<?= (int)$row['stok'] ?>)
                                </span>
                            <?php else: ?>
                                <span class="text-gray-800 font-medium"><?= (int)$row['stok'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <td class="py-3 px-4 text-center space-x-2 aksi-col">
                                <a href="edit.php?id=<?= (int)$row['id'] ?>" class="text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                                <a href="proses.php?action=delete&id=<?= (int)$row['id'] ?>" onclick="return confirm('Yakin ingin menghapus data ini?')" class="text-rose-600 hover:text-rose-800 font-medium">Hapus</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    nav, form, button, .aksi-col {
        display: none !important;
    }
    body { background-color: white !important; }
    main { padding: 0 !important; max-width: 100% !important; }
    .shadow-sm { box-shadow: none !important; border: none !important; }
}
</style>

<?php include 'inventaris/include/footer.php'; ?>
