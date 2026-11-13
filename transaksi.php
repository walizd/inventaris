<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'inventaris/config/database.php';

$error   = '';
$success = '';

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barang_id  = (int)($_POST['barang_id'] ?? 0);
    $jenis      = $_POST['jenis_transaksi'] ?? '';
    $jumlah     = (int)($_POST['jumlah'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');

    // Validasi jenis hanya boleh MASUK atau KELUAR
    $jenis_valid = in_array($jenis, ['MASUK', 'KELUAR']);

    if (!$barang_id || !$jenis_valid || $jumlah <= 0 || empty($keterangan)) {
        $error = "Semua kolom wajib diisi dengan benar dan jumlah harus lebih dari 0.";
    } else {
        try {
            $pdo->beginTransaction();

            // Ambil stok saat ini dengan lock untuk mencegah race condition
            $stmt = $pdo->prepare("SELECT stok, nama_barang FROM barang WHERE id = ? FOR UPDATE");
            $stmt->execute([$barang_id]);
            $barang = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$barang) {
                throw new Exception("Barang tidak ditemukan.");
            }

            $stok_sekarang = (int)$barang['stok'];

            if ($jenis === 'MASUK') {
                $stok_baru = $stok_sekarang + $jumlah;
            } else {
                if ($jumlah > $stok_sekarang) {
                    throw new Exception("Stok tidak mencukupi! Stok saat ini: {$stok_sekarang}");
                }
                $stok_baru = $stok_sekarang - $jumlah;
            }

            // Update stok
            $updateStmt = $pdo->prepare("UPDATE barang SET stok = ? WHERE id = ?");
            $updateStmt->execute([$stok_baru, $barang_id]);

            // Catat ke riwayat_stok
            $insertStmt = $pdo->prepare(
                "INSERT INTO riwayat_stok (barang_id, jenis_transaksi, jumlah, keterangan) VALUES (?, ?, ?, ?)"
            );
            $insertStmt->execute([$barang_id, $jenis, $jumlah, $keterangan]);

            $pdo->commit();
            $success = "Transaksi berhasil dicatat dan stok diperbarui!";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

// Ambil daftar barang untuk dropdown
$stmtBarang   = $pdo->query("SELECT id, nama_barang, stok FROM barang ORDER BY nama_barang ASC");
$daftar_barang = $stmtBarang->fetchAll(PDO::FETCH_ASSOC);

// Ambil riwayat transaksi dengan JOIN ke tabel barang
$stmtRiwayat = $pdo->query("
    SELECT r.*, b.nama_barang
    FROM riwayat_stok r
    JOIN barang b ON r.barang_id = b.id
    ORDER BY r.id DESC
");
$riwayat_list = $stmtRiwayat->fetchAll(PDO::FETCH_ASSOC);

// header.php akan skip session_start() karena session sudah aktif
include 'inventaris/include/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Riwayat & Transaksi Stok</h1>
        <p class="text-sm text-gray-500">Catat barang masuk (restock) atau barang keluar (penjualan/penggunaan).</p>
    </div>
    <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">← Kembali ke Inventaris</a>
</div>

<?php if ($error): ?>
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<!-- Form Input Transaksi -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
    <h2 class="text-lg font-bold text-gray-900 mb-4">Form Transaksi Baru</h2>
    <form action="transaksi.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Barang</label>
            <select name="barang_id" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">-- Pilih Barang --</option>
                <?php foreach ($daftar_barang as $b): ?>
                    <option value="<?= (int)$b['id'] ?>">
                        <?= htmlspecialchars($b['nama_barang']) ?> (Stok: <?= (int)$b['stok'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Transaksi</label>
            <select name="jenis_transaksi" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="MASUK">Barang Masuk (Restock)</option>
                <option value="KELUAR">Barang Keluar (Keluar/Terjual)</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
            <input type="number" name="jumlah" min="1" required placeholder="Contoh: 10"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
            <input type="text" name="keterangan" required placeholder="Contoh: Restock dari supplier"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div class="md:col-span-2 lg:col-span-4 flex justify-end">
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                Simpan Transaksi
            </button>
        </div>
    </form>
</div>

<!-- Tabel Riwayat Transaksi -->
<div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
    <div class="p-4 border-b border-gray-100">
        <h3 class="font-bold text-gray-900">Log Riwayat Pergerakan Stok</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                    <th class="py-3 px-4 font-semibold">Waktu</th>
                    <th class="py-3 px-4 font-semibold">Nama Barang</th>
                    <th class="py-3 px-4 font-semibold">Jenis</th>
                    <th class="py-3 px-4 font-semibold">Jumlah</th>
                    <th class="py-3 px-4 font-semibold">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm">
                <?php if (empty($riwayat_list)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-400">Belum ada riwayat transaksi tercatat.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($riwayat_list as $row): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="py-3 px-4 text-gray-500 text-xs"><?= htmlspecialchars($row['tanggal']) ?></td>
                        <td class="py-3 px-4 font-medium text-gray-900"><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td class="py-3 px-4">
                            <?php if ($row['jenis_transaksi'] === 'MASUK'): ?>
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full text-xs font-semibold">MASUK (+)</span>
                            <?php else: ?>
                                <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-full text-xs font-semibold">KELUAR (-)</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 font-bold"><?= (int)$row['jumlah'] ?></td>
                        <td class="py-3 px-4 text-gray-600"><?= htmlspecialchars($row['keterangan']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'inventaris/include/footer.php'; ?>
