<?php
session_start();
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];

$trxId = trim($_REQUEST['id'] ?? '');
if ($trxId === '') {
    http_response_code(400);
    echo 'ID pesanan diperlukan.';
    exit;
}

$msg = '';
$errors = [];

$stmt = $conn->prepare('SELECT id, Total_harga, Status, id_user FROM transaksi WHERE id = ? LIMIT 1');
$stmt->bind_param('s', $trxId);
$stmt->execute();
$res = $stmt->get_result();
if (!($trx = $res->fetch_assoc())) {
    http_response_code(404);
    echo 'Pesanan tidak ditemukan.';
    exit;
}
$stmt->close();

if ($trx['id_user'] !== $userId) {
    http_response_code(403);
    echo 'Anda tidak diizinkan memproses pesanan ini.';
    exit;
}

if (!in_array(strtolower($trx['Status']), ['menunggu pembayaran', 'pending'])) {
    $msg = 'Pesanan tidak dalam status Menunggu Pembayaran. Status sekarang: ' . htmlspecialchars($trx['Status']);
}

$items = [];
$stmt = $conn->prepare('SELECT ID_Buku, Jumlah, Harga_Satuan FROM detail_transaksi WHERE ID_Transaksi = ?');
$stmt->bind_param('s', $trxId);
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) $items[] = $row;
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($msg)) {
    $metode = $_POST['metode'] ?? 'Transfer Bank';
    $metode = in_array($metode, ['Transfer Bank', 'E-Wallet', 'COD']) ? $metode : 'Transfer Bank';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn->begin_transaction();

        $insStockHistory = $conn->prepare('INSERT INTO stock_history (id_buku, change_qty, reason) VALUES (?, ?, ?)');
        $updBuku = $conn->prepare('UPDATE buku SET Stok = Stok - ? WHERE id = ?');

        foreach ($items as $it) {
            $idBuku = $it['ID_Buku'];
            $need = intval($it['Jumlah']);

            $lock = $conn->prepare('SELECT Stok FROM buku WHERE id = ? FOR UPDATE');
            $lock->bind_param('s', $idBuku);
            $lock->execute();
            $rg = $lock->get_result();
            $b = $rg->fetch_assoc();
            $lock->close();

            $available = intval($b['Stok'] ?? 0);
            if ($available < $need) {
                throw new Exception("Stok untuk buku $idBuku tidak mencukupi. Tersedia: $available, diperlukan: $need");
            }

            $updBuku->bind_param('is', $need, $idBuku);
            $updBuku->execute();

            $reason = 'Penjualan: ' . $trxId;
            $changeQty = -1 * $need;
            $insStockHistory->bind_param('sis', $idBuku, $changeQty, $reason);
            $insStockHistory->execute();
        }

        $stmt = $conn->prepare('UPDATE transaksi SET Status = ? WHERE id = ?');
        $newStatus = 'Dibayar';
        $stmt->bind_param('ss', $newStatus, $trxId);
        $stmt->execute();
        $stmt->close();

        try {
            $stmt = $conn->prepare('INSERT INTO pembayaran (ID_Transaksi, Metode, Status, Tanggal_Bayar) VALUES (?, ?, ?, CURDATE())');
            $payStatus = 'Berhasil';
            $stmt->bind_param('sss', $trxId, $metode, $payStatus);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = 'Catatan pembayaran gagal disimpan: ' . $e->getMessage();
        }

        $stmt = $conn->prepare('DELETE FROM wishlist WHERE id_user = ?');
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        $msg = '✅ Pembayaran berhasil! Pesanan telah diupdate menjadi: ' . $newStatus;
    } catch (Exception $e) {
        $conn->rollback();
        $errors[] = '❌ Pembayaran gagal: ' . $e->getMessage();
    } finally {
        mysqli_report(MYSQLI_REPORT_OFF);
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
    .bayar-page {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background-color: #f5f6fa;
        color: #333;
        padding: 40px 0;
    }
    .bayar-page .container {
        max-width: 800px;
        margin: 0 auto;
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .bayar-page h2, .bayar-page h3 {
        color: #2c3e50;
        margin-bottom: 15px;
    }
    .bayar-page table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        background: #fafafa;
    }
    .bayar-page th, .bayar-page td {
        padding: 10px 12px;
        border: 1px solid #ddd;
        text-align: center;
    }
    .bayar-page th {
        background-color: #2e7d32;
        color: #fff;
    }
    .bayar-page tfoot td {
        background-color: #f0f0f0;
        font-weight: bold;
    }
    .bayar-page .msg-success {
        background: #e8f9e8;
        border: 1px solid #2ecc71;
        color: #2e7d32;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .bayar-page .msg-error {
        background: #fdecea;
        border: 1px solid #e74c3c;
        color: #c0392b;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .bayar-page button {
        background-color: #2e7d32;
        color: white;
        border: none;
        padding: 10px 18px;
        font-size: 15px;
        border-radius: 6px;
        cursor: pointer;
        transition: 0.3s;
    }
    .bayar-page button:hover {
        background-color: #2e7d32;
    }
    .bayar-page label {
        display: block;
        margin: 6px 0;
    }
    .bayar-page a {
        color: #2e7d32;
        text-decoration: none;
    }
    .bayar-page a:hover {
        text-decoration: underline;
    }
</style>

<div class="bayar-page">
    <div class="container">
        <h2>💳 Bayar Pesanan #<?php echo htmlspecialchars($trxId); ?></h2>

        <?php if (!empty($msg)): ?>
            <div class="msg-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="msg-error">
                <ul>
                    <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
                </ul>
            </div>
        <?php endif; ?>

        <h3>📦 Ringkasan Pesanan</h3>
        <table>
            <thead>
                <tr>
                    <th>ID Buku</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php $sum=0; foreach ($items as $it): 
                $line = $it['Jumlah'] * $it['Harga_Satuan']; $sum += $line; ?>
                <tr>
                    <td><?php echo htmlspecialchars($it['ID_Buku']); ?></td>
                    <td><?php echo (int)$it['Jumlah']; ?></td>
                    <td>Rp <?php echo number_format($it['Harga_Satuan'],0,',','.'); ?></td>
                    <td>Rp <?php echo number_format($line,0,',','.'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right;">Total:</td>
                    <td>Rp <?php echo number_format($sum,0,',','.'); ?></td>
                </tr>
            </tfoot>
        </table>

        <?php if (empty($msg)): ?>
            <h3>💰 Pilih Metode Pembayaran</h3>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($trxId); ?>">
                <label><input type="radio" name="metode" value="Transfer Bank" checked> Transfer Bank</label>
                <label><input type="radio" name="metode" value="E-Wallet"> E-Wallet</label>
                <label><input type="radio" name="metode" value="COD"> COD (Bayar di tempat)</label>
                <p style="margin-top:15px;">
                    <button type="submit">💳 Bayar Sekarang</button>
                </p>
            </form>
        <?php else: ?>
            <p><a href="index.php">← Kembali ke Beranda</a></p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
