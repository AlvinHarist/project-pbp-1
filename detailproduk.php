<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="css/style.css"> <!-- umum -->
<link rel="stylesheet" href="css/detailproduk.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<?php
$id = isset($_GET['id']) ? $_GET['id'] : '';

// prepared statement to fetch book
$buku = null;
if ($id !== '') {
    $stmt = $conn->prepare("SELECT * FROM buku WHERE id = ? LIMIT 1");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $buku = $res->fetch_assoc();
    }
    $stmt->close();
}

// Query review (prepared)
$result_review = null;
if ($buku) {
    $stmt_r = $conn->prepare("SELECT * FROM review WHERE ID_Buku = ? ORDER BY Tanggal DESC");
    $stmt_r->bind_param('s', $id);
    $stmt_r->execute();
    $result_review = $stmt_r->get_result();
    $stmt_r->close();
} else {
    echo "Buku tidak ditemukan!";
    include 'includes/footer.php';
    exit;
}
?>

<main class="book-detail">
    <div class="container">
        <div class="detail-container">
            <div class="detail-left">
                <?php
                $imgJpg = 'images/' . $buku['id'] . '.jpg';
                $imgPng = 'images/' . $buku['id'] . '.png';
                if (file_exists($imgJpg)) { $imgPath = $imgJpg; }
                elseif (file_exists($imgPng)) { $imgPath = $imgPng; }
                else { $imgPath = 'images/empty.png'; }
                ?>
                <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($buku['Judul']); ?>">
                </div>
                <div class="detail-right">
                <h1 class="detail-title"><?php echo htmlspecialchars($buku['Judul']); ?></h1>
                <p class="detail-author">Penulis: <?php echo htmlspecialchars($buku['Penulis']); ?></p>
                <p class="detail-price">Rp <?php echo number_format($buku['Harga'], 0, ',', '.'); ?></p>
                <p class="detail-stock">Stok: <?php echo $buku['Stok']; ?></p>

                <div class="detail-quantity">
                    <label for="qty">Jumlah:</label>
                    <div class="quantity-controls">
                        <button class="quantity-btn minus" onclick="decreaseQty()">-</button>
                        <input type="number" id="qty" name="qty" value="1" min="1" max="<?php echo $buku['Stok']; ?>" class="quantity-input">
                        <button class="quantity-btn plus" onclick="increaseQty()">+</button>
                    </div>
                </div>

                <div class="detail-buttons">
                    <button class="btn-add-cart"><i class="fas fa-shopping-cart"></i> Tambah ke Keranjang</button>
                    <button class="btn-buy-now">Beli Sekarang</button>
                </div>
            </div>
        </div>
        
        <div class="detail-description">
            <div class="detail-tabs">
                <button class="tab active" onclick="showTab(event, 'description')">Deskripsi</button>
                <button class="tab" onclick="showTab(event, 'review')">Review</button>
            </div>
            <div class="tab-content" id="description-content">
                <p><?php echo nl2br(htmlspecialchars($buku['Deskripsi'])); ?></p>
            </div>
            <div class="tab-content" id="review-content" style="display: none;">
                <?php
                if ($result_review->num_rows > 0) {
                    while ($review = $result_review->fetch_assoc()) {
                        echo '<div class="single-review">';
                        // Tampilkan rating bintang
                        echo '<p>';
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $review['Rating']) {
                                echo '<i class="fas fa-star" style="color: gold;"></i> ';
                            } else {
                                echo '<i class="far fa-star" style="color: gold;"></i> ';
                            }
                        }
                        echo '</p>';
                        echo '<p><strong>' . htmlspecialchars($review['ID_User']) . '</strong> (' . $review['Tanggal'] . ')</p>';
                        echo '<p>' . nl2br(htmlspecialchars($review['Komentar'])) . '</p>';
                        echo '<hr>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Belum ada review untuk buku ini.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</main>

<script>
function increaseQty() {
    let qty = document.getElementById('qty');
    let max = parseInt(qty.getAttribute('max'));
    if (parseInt(qty.value) < max) {
        qty.value = parseInt(qty.value) + 1;
    }
}

function decreaseQty() {
    let qty = document.getElementById('qty');
    if (parseInt(qty.value) > 1) {
        qty.value = parseInt(qty.value) - 1;
    }
}

/*function showTab(tabName) {
    // Hide all tab contents
    document.getElementById('description-content').style.display = 'none';
    document.getElementById('review-content').style.display = 'none';
    
    // Remove active class from all tabs
    let tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Show selected tab content
    document.getElementById(tabName + '-content').style.display = 'block';
    
    // Add active class to clicked tab
    event.target.classList.add('active');
}*/

function showTab(e, tabName) {
    // Hide all tab contents
    document.getElementById('description-content').style.display = 'none';
    document.getElementById('review-content').style.display = 'none';
    
    // Remove active class from all tabs
    let tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Show selected tab content
    document.getElementById(tabName + '-content').style.display = 'block';
    
    // Add active class to clicked tab
    e.target.classList.add('active');
}

</script>

<?php include 'includes/footer.php'; ?>