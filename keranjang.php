<?php
// keranjang.php - cart page where checkout creates a pending order (Menunggu Pembayaran)
session_start();
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];

$action = $_REQUEST['action'] ?? '';
$response = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $id_buku = $_POST['id_buku'] ?? '';
        $jumlah = intval($_POST['jumlah'] ?? 1);
        if ($id_buku === '') $errors[] = 'ID buku tidak valid.';
        if ($jumlah < 1) $errors[] = 'Jumlah harus minimal 1.';

        if (empty($errors)) {
            // check available stock
            $stmtS = $conn->prepare('SELECT Stok FROM buku WHERE id = ? LIMIT 1');
            $stmtS->bind_param('s', $id_buku);
            $stmtS->execute();
            $resS = $stmtS->get_result();
            $rowS = $resS->fetch_assoc();
            $available = intval($rowS['Stok'] ?? 0);
                                // current in cart
                                $stmtC = $conn->prepare('SELECT Jumlah FROM wishlist WHERE id_buku = ? AND id_user = ? LIMIT 1');
                                $stmtC->bind_param('ss', $id_buku, $userId);
                                $stmtC->execute();
                                $resC = $stmtC->get_result();
                                $currentInCart = 0;
                                if ($resC && $resC->num_rows > 0) $currentInCart = intval($resC->fetch_assoc()['Jumlah']);
                                $stmtC->close();

                                if (($currentInCart + $jumlah) > $available) {
                                    $errors[] = "Stok tidak mencukupi. Tersedia: $available, Anda mencoba: " . ($currentInCart + $jumlah);
                                } else {
                                    // add or update wishlist
                                    $stmt = $conn->prepare('SELECT Jumlah FROM wishlist WHERE id_buku = ? AND id_user = ? LIMIT 1');
                                    $stmt->bind_param('ss', $id_buku, $userId);
                                    $stmt->execute();
                                    $r = $stmt->get_result();
                                    if ($r && $r->num_rows > 0) {
                                        $row = $r->fetch_assoc();
                                        $newJumlah = $row['Jumlah'] + $jumlah;
                                        $stmt2 = $conn->prepare('UPDATE wishlist SET Jumlah = ? WHERE id_buku = ? AND id_user = ?');
                                        $stmt2->bind_param('iss', $newJumlah, $id_buku, $userId);
                                        $stmt2->execute();
                                        $stmt2->close();
                                    } else {
                                        $stmt2 = $conn->prepare('INSERT INTO wishlist (Jumlah, id_buku, id_user) VALUES (?, ?, ?)');
                                        $stmt2->bind_param('iss', $jumlah, $id_buku, $userId);
                                        $stmt2->execute();
                                        $stmt2->close();
                                    }
                                    $stmt->close();
                                    $response = 'Berhasil menambahkan ke keranjang.';
                                }
                            }
                        } elseif ($action === 'update') {
                            if (!empty($_POST['remove'])) {
                                $wishToRemove = intval($_POST['remove']);
                                $stmt = $conn->prepare('DELETE FROM wishlist WHERE id = ? AND id_user = ?');
                                $stmt->bind_param('is', $wishToRemove, $userId);
                                $stmt->execute();
                                $stmt->close();
                                $response = 'Item dihapus.';
                            } else {
                                $updates = $_POST['qty'] ?? [];
                                foreach ($updates as $wishId => $qty) {
                                    $wishId = intval($wishId);
                                    $qty = intval($qty);

                                    // find buku id for this wishlist row
                                    $stmtx = $conn->prepare('SELECT id_buku FROM wishlist WHERE id = ? AND id_user = ? LIMIT 1');
                                    $stmtx->bind_param('is', $wishId, $userId);
                                    $stmtx->execute();
                                    $resx = $stmtx->get_result();
                                    $idBuku = null;
                                    if ($resx && $resx->num_rows > 0) $idBuku = $resx->fetch_assoc()['id_buku'];
                                    $stmtx->close();

                                    if ($idBuku !== null) {
                                        $stmtS = $conn->prepare('SELECT Stok FROM buku WHERE id = ? LIMIT 1');
                                        $stmtS->bind_param('s', $idBuku);
                                        $stmtS->execute();
                                        $resS = $stmtS->get_result();
                                        $rowS = $resS->fetch_assoc();
                                        $available = intval($rowS['Stok'] ?? 0);
                                        $stmtS->close();
                                        if ($qty > $available) {
                                            $errors[] = "Stok untuk salah satu produk tidak mencukupi. Tersedia: $available, Permintaan: $qty";
                                            continue;
                                        }
                                    }

                                    if ($qty <= 0) {
                                        $stmt = $conn->prepare('DELETE FROM wishlist WHERE id = ? AND id_user = ?');
                                        $stmt->bind_param('is', $wishId, $userId);
                                        $stmt->execute();
                                        $stmt->close();
                                    } else {
                                        $stmt = $conn->prepare('UPDATE wishlist SET Jumlah = ? WHERE id = ? AND id_user = ?');
                                        $stmt->bind_param('iis', $qty, $wishId, $userId);
                                        $stmt->execute();
                                        $stmt->close();
                                    }
                                }
                                $response = 'Keranjang diperbarui.';
                            }
                        } elseif ($action === 'remove') {
                            $wishId = intval($_POST['wish_id'] ?? 0);
                            if ($wishId > 0) {
                                $stmt = $conn->prepare('DELETE FROM wishlist WHERE id = ? AND id_user = ?');
                                $stmt->bind_param('is', $wishId, $userId);
                                $stmt->execute();
                                $stmt->close();
                                $response = 'Item dihapus.';
                            }
                        } elseif ($action === 'checkout') {
                            $alamat = trim($_POST['alamat'] ?? '');
                            if ($alamat === '') $errors[] = 'Alamat harus diisi untuk checkout.';

                            // fetch cart
                            $cart = [];
                            $stmt = $conn->prepare('SELECT w.id, w.Jumlah, w.id_buku, b.Harga, b.Judul, b.Stok FROM wishlist w JOIN buku b ON w.id_buku = b.id WHERE w.id_user = ?');
                            $stmt->bind_param('s', $userId);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            while ($r = $res->fetch_assoc()) $cart[] = $r;
                            $stmt->close();

                            if (empty($cart)) $errors[] = 'Keranjang kosong.';

                            if (empty($errors)) {
                                // calculate total
                                $trxId = 'T' . time();
                                $total = 0;
                                foreach ($cart as $c) $total += ($c['Harga'] * $c['Jumlah']);

                                // create transaksi and detail rows inside transaction, but DO NOT decrement stock yet
                                try {
                                    $conn->begin_transaction();

                                    $stmt = $conn->prepare('INSERT INTO transaksi (id, Tanggal, Total_harga, Status, id_user) VALUES (?, CURDATE(), ?, ?, ?)');
                                    if (!$stmt) throw new Exception('Prepare transaksi failed');
                                    $status = 'Menunggu Pembayaran';
                                    $stmt->bind_param('siss', $trxId, $total, $status, $userId);
                                    if (!$stmt->execute()) throw new Exception('Execute transaksi failed');
                                    $stmt->close();

                                    foreach ($cart as $c) {
                                        $stmt = $conn->prepare('INSERT INTO detail_transaksi (ID_Transaksi, ID_Buku, Jumlah, Harga_Satuan) VALUES (?, ?, ?, ?)');
                                        if (!$stmt) throw new Exception('Prepare detail_transaksi failed');
                                        $stmt->bind_param('ssis', $trxId, $c['id_buku'], $c['Jumlah'], $c['Harga']);
                                        if (!$stmt->execute()) throw new Exception('Execute detail_transaksi failed');
                                        $stmt->close();
                                    }

                                    $conn->commit();

                                    // capture new order id so UI can link to payment
                                    $newOrderId = $trxId;

                                    // write email stub
                                    $emailDir = __DIR__ . '/emails';
                                    if (!is_dir($emailDir)) @mkdir($emailDir, 0755, true);
                                    $emailFile = $emailDir . '/order_' . $trxId . '.html';
                                    $emailHtml = '<html><body>';
                                    $emailHtml .= '<h2>Order ' . htmlspecialchars($trxId) . '</h2>';
                                    $emailHtml .= '<p>User: ' . htmlspecialchars($user['Email'] ?? $user['id']) . '</p>';
                                    $emailHtml .= '<ul>';
                                    foreach ($cart as $c) {
                                        $emailHtml .= '<li>' . htmlspecialchars($c['Judul']) . ' x ' . intval($c['Jumlah']) . ' = Rp ' . number_format($c['Harga'] * $c['Jumlah'], 0, ',', '.') . '</li>';
                                    }
                                    $emailHtml .= '</ul>';
                                    $emailHtml .= '<p>Total: Rp ' . number_format($total,0,',','.') . '</p>';
                                    $emailHtml .= '<p>Status: Menunggu Pembayaran</p>';
                                    $emailHtml .= '</body></html>';
                                    @file_put_contents($emailFile, $emailHtml);

                                    $response = 'Pesanan dibuat. Silakan lakukan pembayaran. ID Pesanan: ' . $trxId;
                                } catch (Exception $e) {
                                    $conn->rollback();
                                    $errors[] = 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage();
                                }
                            }
                        }
                    }

                    // fetch cart items for display
                    $cartItems = [];
                    $stmt = $conn->prepare('SELECT w.id, w.Jumlah, w.id_buku, b.Judul, b.Harga, b.Stok FROM wishlist w JOIN buku b ON w.id_buku = b.id WHERE w.id_user = ?');
                    $stmt->bind_param('s', $userId);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($r = $res->fetch_assoc()) $cartItems[] = $r;
                    $stmt->close();

                    // render page
                    ?>
                    <?php include 'includes/header.php'; ?>
                    <div class="container" style="padding:40px 0;">
                        <h2>Keranjang Saya</h2>

                        <!-- Toast container for nice notifications -->
                        <div id="toast-container" aria-live="polite" aria-atomic="true" style="position:fixed; top:16px; right:16px; z-index:9999;"></div>

                        <?php
                            // Prepare messages for JS (safe encoding)
                            $jsResponse = !empty($response) ? $response : null;
                            $jsNewOrderId = $newOrderId ?? null;
                            $jsErrors = !empty($errors) ? $errors : [];
                        ?>
                        <script>
                        (function(){
                            const response = <?php echo json_encode($jsResponse); ?>;
                            const newOrderId = <?php echo json_encode($jsNewOrderId); ?>;
                            const errors = <?php echo json_encode($jsErrors); ?>;

                            // simple toast creator
                            function showToast(message, type='success', autoHide=5000, html=false){
                                const container = document.getElementById('toast-container');
                                if (!container) return;
                                const t = document.createElement('div');
                                t.className = 'toast ' + type;
                                t.style.minWidth = '280px';
                                t.style.marginBottom = '10px';
                                t.style.padding = '12px 16px';
                                t.style.borderRadius = '6px';
                                t.style.color = '#fff';
                                t.style.boxShadow = '0 4px 12px rgba(0,0,0,0.12)';
                                t.style.opacity = '0';
                                t.style.transition = 'opacity 240ms ease, transform 240ms ease';
                                t.style.transform = 'translateY(-6px)';
                                if (html) t.innerHTML = message; else t.textContent = message;
                                if (type === 'success') t.style.background = '#2e7d32';
                                if (type === 'error') t.style.background = '#c62828';

                                container.appendChild(t);
                                // show
                                requestAnimationFrame(()=>{ t.style.opacity='1'; t.style.transform='translateY(0)'; });
                                if (autoHide) setTimeout(()=>{ hideToast(t); }, autoHide);
                                return t;
                            }
                            function hideToast(el){
                                el.style.opacity='0'; el.style.transform='translateY(-6px)';
                                setTimeout(()=>{ if (el && el.parentNode) el.parentNode.removeChild(el); }, 260);
                            }

                            // show response as success toast and include payment link if available
                            if (response) {
                                let html = false;
                                let content = response;
                                if (newOrderId) {
                                    const link = '<a class="toast-link" href="bayar.php?id=' + encodeURIComponent(newOrderId) + '" style="color:#fff; text-decoration:underline; margin-left:8px;">Bayar Sekarang</a>';
                                    content = response + ' ' + link;
                                    html = true;
                                }
                                showToast(content, 'success', 6000, html);

                                // auto-redirect after 3s if there is a newOrderId
                                if (newOrderId) setTimeout(function(){ window.location.href = 'bayar.php?id=' + encodeURIComponent(newOrderId); }, 3000);
                            }

                            // show each error as an error toast
                            if (errors && errors.length) {
                                errors.forEach(function(e, i){
                                    // stagger toasts a bit
                                    setTimeout(function(){ showToast(e, 'error', 8000, false); }, i * 300);
                                });
                            }
                        })();
                        </script>

                        <style>
                        /* Toast styles (scoped inline so no extra files needed) */
                        #toast-container .toast { font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial; }
                        #toast-container .toast a.toast-link { color: #fff; font-weight:600; }
                        </style>

                        <?php if (empty($cartItems)): ?>
                            <p>Keranjang kosong.</p>
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="action" value="update">
                                <table style="width:100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left; border-bottom:1px solid #ddd; padding:8px">Produk</th>
                                            <th style="border-bottom:1px solid #ddd; padding:8px">Harga</th>
                                            <th style="border-bottom:1px solid #ddd; padding:8px">Jumlah</th>
                                            <th style="border-bottom:1px solid #ddd; padding:8px">Subtotal</th>
                                            <th style="border-bottom:1px solid #ddd; padding:8px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $grand = 0; foreach ($cartItems as $item): $subtotal = $item['Harga'] * $item['Jumlah']; $grand += $subtotal; ?>
                                        <tr data-id="<?php echo (int)$item['id']; ?>" data-price="<?php echo (float)$item['Harga']; ?>" data-stock="<?php echo (int)($item['Stok'] ?? 0); ?>">
                                            <td style="padding:8px">
                                                <?php echo htmlspecialchars($item['Judul']); ?>
                                                <div class="stock-warning" style="display:none; color:#b71c1c; font-size:13px; margin-top:6px;"><?php echo 'Stok tidak mencukupi.'; ?></div>
                                            </td>
                                            <td style="padding:8px">Rp <?php echo number_format($item['Harga'],0,',','.'); ?></td>
                                            <td style="padding:8px; width:120px"><input type="number" name="qty[<?php echo (int)$item['id']; ?>]" value="<?php echo (int)$item['Jumlah']; ?>" min="0" style="width:70px"></td>
                                            <td style="padding:8px"><span class="subtotal">Rp <?php echo number_format($subtotal,0,',','.'); ?></span></td>
                                            <td style="padding:8px">
                                                <!-- single remove button submits inside the update form -->
                                                <button type="submit" name="remove" value="<?php echo (int)$item['id']; ?>">Hapus</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" style="text-align:right; padding:8px"><strong>Total:</strong></td>
                                            <td style="padding:8px"><strong>Rp <?php echo number_format($grand,0,',','.'); ?></strong></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <p style="margin-top:12px"><button type="submit">Update Keranjang</button></p>
                            </form>

                            <h3>Checkout</h3>
                            <form method="post">
                                <input type="hidden" name="action" value="checkout">
                                <div style="margin-bottom:8px">
                                    <label for="alamat">Alamat Pengiriman</label><br>
                                    <textarea name="alamat" id="alamat" rows="3" style="width:100%"></textarea>
                                </div>
                                <button type="submit">Checkout Sekarang</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <?php include 'includes/footer.php'; ?>

                    <script>
                    document.addEventListener('DOMContentLoaded', function(){
                        function formatRp(n){
                            return 'Rp ' + n.toLocaleString('id-ID');
                        }

                        const rows = document.querySelectorAll('tr[data-id]');
                        function recalc(){
                            let grand = 0;
                            let hasStockError = false;
                            rows.forEach(row => {
                                const price = parseFloat(row.dataset.price) || 0;
                                const stock = parseInt(row.dataset.stock) || 0;
                                const input = row.querySelector('input[type="number"]');
                                const qty = parseInt(input.value) || 0;
                                const subtotal = price * qty;
                                grand += subtotal;
                                const subtotalEl = row.querySelector('.subtotal');
                                if (subtotalEl) subtotalEl.textContent = formatRp(subtotal);
                                const warn = row.querySelector('.stock-warning');
                                if (qty > stock) {
                                    hasStockError = true;
                                    if (warn) warn.style.display = 'block';
                                } else {
                                    if (warn) warn.style.display = 'none';
                                }
                            });
                            const totalCell = document.querySelector('tfoot tr td strong');
                            if (totalCell) totalCell.textContent = formatRp(grand);
                            // disable checkout if any stock error
                            const checkoutForm = document.querySelector('form input[name="action"][value="checkout"]')?.closest('form');
                            if (checkoutForm) {
                                const checkoutBtn = checkoutForm.querySelector('button[type="submit"]');
                                if (checkoutBtn) checkoutBtn.disabled = hasStockError;
                            }
                        }

                        document.querySelectorAll('input[type="number"]').forEach(inp => {
                            inp.addEventListener('change', recalc);
                            inp.addEventListener('input', recalc);
                        });
                        // initial recalc to ensure format
                        recalc();
                    });
                    </script>
            const stock = parseInt(row.dataset.stock) || 0;
