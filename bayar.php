<?php
// bayar.php - proses pembayaran untuk pesanan (menyelesaikan pesanan pending)
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

// fetch transaksi
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

// basic ownership check (buyers can only pay their orders)
if ($trx['id_user'] !== $userId) {
    http_response_code(403);
    echo 'Anda tidak diizinkan memproses pesanan ini.';
    exit;
}

// only allow payments for pending/pembayaran-waiting statuses
if (strtolower($trx['Status']) !== strtolower('Menunggu Pembayaran') && strtolower($trx['Status']) !== strtolower('Pending')) {
    $msg = 'Pesanan tidak dalam status Menunggu Pembayaran. Status sekarang: ' . htmlspecialchars($trx['Status']);
}

// fetch order items
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

    // begin mysql transaction and process
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn->begin_transaction();

        // lock and verify stock for each buku
        $insStockHistory = $conn->prepare('INSERT INTO stock_history (id_buku, change_qty, reason) VALUES (?, ?, ?)');
        $updBuku = $conn->prepare('UPDATE buku SET Stok = Stok - ? WHERE id = ?');

        foreach ($items as $it) {
            $idBuku = $it['ID_Buku'];
            $need = intval($it['Jumlah']);

            // lock row
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

            // decrement stock
            $updBuku->bind_param('is', $need, $idBuku);
            if (!$updBuku->execute()) throw new Exception('Gagal mengurangi stok untuk ' . $idBuku);

            // record stock history (negative change)
            $reason = 'Penjualan: ' . $trxId;
            $changeQty = -1 * $need;
            $insStockHistory->bind_param('sis', $idBuku, $changeQty, $reason);
            $insStockHistory->execute();
        }

        // mark transaksi as Dibayar
        $stmt = $conn->prepare('UPDATE transaksi SET Status = ? WHERE id = ?');
        $newStatus = 'Dibayar';
        $stmt->bind_param('ss', $newStatus, $trxId);
        $stmt->execute();
        $stmt->close();

        // insert into pembayaran table if possible (attempt; don't fail entire flow if table differs)
        try {
            $stmt = $conn->prepare('INSERT INTO pembayaran (ID_Transaksi, Metode, Status, Tanggal_Bayar) VALUES (?, ?, ?, CURDATE())');
            $payStatus = 'Berhasil';
            $stmt->bind_param('sss', $trxId, $metode, $payStatus);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            // non-fatal: payment log failed, but order processing succeeded
            // store a warning but continue
            $errors[] = 'Catatan pembayaran gagal disimpan: ' . $e->getMessage();
        }

        // clear wishlist for this user
        $stmt = $conn->prepare('DELETE FROM wishlist WHERE id_user = ?');
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        // write email stub for payment confirmation
        $emailDir = __DIR__ . '/emails';
        if (!is_dir($emailDir)) @mkdir($emailDir, 0755, true);
        $emailFile = $emailDir . '/payment_' . $trxId . '.html';
        $html = '<html><body>';
        $html .= '<h2>Pembayaran Diterima - ' . htmlspecialchars($trxId) . '</h2>';
        $html .= '<p>User: ' . htmlspecialchars($user['Email'] ?? $user['id']) . '</p>';
        $html .= '<ul>';
        $total = 0;
        foreach ($items as $it) {
            $lineTotal = $it['Jumlah'] * $it['Harga_Satuan'];
            $total += $lineTotal;
            $html .= '<li>' . htmlspecialchars($it['ID_Buku']) . ' x ' . intval($it['Jumlah']) . ' = Rp ' . number_format($lineTotal,0,',','.') . '</li>';
        }
        $html .= '</ul>';
        $html .= '<p>Total dibayar: Rp ' . number_format($total,0,',','.') . '</p>';
        $html .= '<p>Status pesanan: ' . htmlspecialchars($newStatus) . '</p>';
        if (!empty($errors)) {
            $html .= '<p><strong>Catatan:</strong></p><ul>';
            foreach ($errors as $er) $html .= '<li>' . htmlspecialchars($er) . '</li>';
            $html .= '</ul>';
        }
        $html .= '</body></html>';
        @file_put_contents($emailFile, $html);

        $msg = 'Pembayaran berhasil, pesanan diupdate menjadi: ' . $newStatus;
    } catch (Exception $e) {
        $conn->rollback();
        $errors[] = 'Pembayaran gagal: ' . $e->getMessage();
    } finally {
        // restore mysqli error reporting to defaults (optional)
        mysqli_report(MYSQLI_REPORT_OFF);
    }
}

// render page
?>
<?php include 'includes/header.php'; ?>
<div class="container" style="padding:40px 0;">
    <h2>Bayar Pesanan <?php echo htmlspecialchars($trxId); ?></h2>

    <?php if (!empty($msg)) echo '<p style="color:green">' . htmlspecialchars($msg) . '</p>'; ?>
    <?php if (!empty($errors)) { echo '<div style="color:red"><ul>'; foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; echo '</ul></div>'; } ?>

    <h3>Ringkasan Pesanan</h3>
    <table style="width:100%; border-collapse:collapse">
        <thead>
            <tr><th>Produk</th><th>Jumlah</th><th>Harga Satuan</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
        <?php $sum=0; foreach ($items as $it): $line = $it['Jumlah'] * $it['Harga_Satuan']; $sum += $line; ?>
            <tr>
                <td><?php echo htmlspecialchars($it['ID_Buku']); ?></td>
                <td><?php echo (int)$it['Jumlah']; ?></td>
                <td>Rp <?php echo number_format($it['Harga_Satuan'],0,',','.'); ?></td>
                <td>Rp <?php echo number_format($line,0,',','.'); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="3" style="text-align:right"><strong>Total:</strong></td><td><strong>Rp <?php echo number_format($sum,0,',','.'); ?></strong></td></tr>
        </tfoot>
    </table>

    <?php if (empty($msg)): ?>
    <h3>Metode Pembayaran</h3>
    <form method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($trxId); ?>">
        <label><input type="radio" name="metode" value="Transfer Bank" checked> Transfer Bank</label><br>
        <label><input type="radio" name="metode" value="E-Wallet"> E-Wallet</label><br>
        <label><input type="radio" name="metode" value="COD"> COD (Bayar di tempat)</label><br>
        <p style="margin-top:12px"><button type="submit">Bayar Sekarang</button></p>
    </form>
    <?php else: ?>
        <p><a href="index.php">Kembali ke Beranda</a></p>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
