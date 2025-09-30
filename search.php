<?php
include 'includes/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo '<div class="container" style="padding:40px 0;"><p>Masukkan kata kunci pencarian.</p></div>';
    include 'includes/footer.php';
    exit;
}

// gunakan model & controller baru
$bookModel = new BookModel($conn);
$searchController = new SearchController($bookModel);
$rows = $searchController->handleSearch($q);

?>

<section class="browse-by-category">
    <div class="container">
        <div class="section-title">
            <h2>Hasil Pencarian untuk "<?php echo htmlspecialchars($q); ?>"</h2>
        </div>

        <div class="books-grid">
            <?php if (!empty($rows)): ?>
                <?php foreach($rows as $row): ?>
                    <div class="book-card">
                        <a href="detailproduk.php?id=<?php echo $row['id']; ?>" class="book-link" style="color: inherit; text-decoration: none;">
                            <div class="book-image-container">
                                <?php
                                $imgJpg = 'images/' . $row['id'] . '.jpg';
                                $imgPng = 'images/' . $row['id'] . '.png';
                                if (file_exists($imgJpg)) {
                                    $imgPath = $imgJpg;
                                } elseif (file_exists($imgPng)) {
                                    $imgPath = $imgPng;
                                } else {
                                    $imgPath = 'images/empty.png';
                                }
                                ?>
                                <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($row['Judul']); ?>">
                            </div>
                            <div class="book-info">
                                <p class="category">Rp <?php echo number_format($row['Harga'], 0, ',', '.'); ?></p>
                                <h3><?php echo htmlspecialchars($row['Judul']); ?></h3>
                                <p>by <?php echo htmlspecialchars($row['Penulis']); ?></p>
                            </div>
                        </a>
                        <div class="book-footer">
                            <p class="book-price">Rp <?php echo number_format($row['Harga'], 0, ',', '.'); ?></p>
                            <button class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i> Add</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Produk tidak ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
