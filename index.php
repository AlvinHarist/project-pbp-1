<?php include 'includes/header.php'; ?>
<?php include "config.php"?>


<?php
$sql = "SELECT * FROM buku ORDER BY Tanggal_Masuk DESC LIMIT 5";
$result = $conn->query($sql);
?>



<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Buka Buku, <br>Buka Jendela Dunia</h1>
                <p>Toko buku pilihanmu! Jelajahi rak-rak buku terpopuler hanya dari genggaman tanganmu ^^</p>
                <div class="hero-buttons">
                    <a href="#" class="btn btn-primary">Browse Collection</a>
                    <a href="#" class="btn btn-secondary">New Arrivals</a>
                </div>
                <div class="stats">
                    <div><strong>50,000+</strong><span>Buku Tersedia</span></div>
                    <div><strong>15,000+</strong><span>Pelanggan</span></div>
                    <div><strong>500+</strong><span>Penulis</span></div>
                </div>
            </div>
            <div class="hero-image">
                <img src="images/keajaiban-toko-kelontong-namiya.jpg" alt="Beautiful library with many books">
                <div class="book-of-the-month">
                    <div class="book-icon"></div>
                    <div>
                        <p>Book of the Month</p>
                        <strong>Keajaiban Toko<br>Kelontong Namiya</strong>
                        <span><i class="fas fa-star"></i> 4.8 (2.1k reviews)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="browse-by-category">
    <div class="container">
        <div class="section-title">
            <h2>Browse by Category</h2>
            <p>Discover books across all genres and find your next favorite read</p>
        </div>
        <div class="category-grid">
            <div class="category-card">
                <i class="fas fa-book-open icon-fiction"></i>
                <h3>Fiction</h3>
                <span>12,450 books</span>
            </div>
            <div class="category-card">
                <i class="fas fa-heart icon-romance"></i>
                <h3>Romance</h3>
                <span>8,230 books</span>
            </div>
            <div class="category-card">
                <i class="fas fa-bolt icon-thriller"></i>
                <h3>Thriller</h3>
                <span>5,670 books</span>
            </div>
             <div class="category-card">
                <i class="fas fa-rocket icon-fantasy"></i>
                <h3>Fantasy</h3>
                <span>7,890 books</span>
            </div>
            <div class="category-card">
                <i class="fas fa-landmark icon-history"></i>
                <h3>History</h3>
                <span>4,320 books</span>
            </div>
             <div class="category-card">
                <i class="fas fa-flask icon-science"></i>
                <h3>Science</h3>
                <span>3,450 books</span>
            </div>
             <div class="category-card">
                <i class="fas fa-palette icon-art"></i>
                <h3>Art & Design</h3>
                <span>2,180 books</span>
            </div>
             <div class="category-card">
                <i class="fas fa-user-edit icon-biography"></i>
                <h3>Biography</h3>
                <span>3,890 books</span>
            </div>
        </div>
    </div>
</section>

<div class="featured-books">
    <div class="container">
        <div class="section-header">
            <h2>Featured Books</h2>
            <a href="#" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="books-grid">

            <?php
            // Query untuk mengambil 5 buku terbaru
            $sql = "SELECT * FROM buku ORDER BY Tanggal_Masuk DESC LIMIT 5";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
            ?>
                <div class="book-card" data-id="<?php echo htmlspecialchars($row['id']); ?>">
                    <a href="detailproduk.php?id=<?php echo $row['id']; ?>" class="book-card-link">
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
                        <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($row['Judul']); ?>" loading="lazy">
                        </div>
                        <div class="book-info">
                            <!-- <p class="category">Rp <?php echo number_format($row['Harga'], 0, ',', '.'); ?></p> -->
                            <h3><?php echo htmlspecialchars($row['Judul']); ?></h3>
                            <p>by <?php echo htmlspecialchars($row['Penulis']); ?></p>
                            <div class="book-rating">
                                <i class="fas fa-star"></i> 4.4 (4532)
                            </div>
                        </div>
                    </a>
                    <div class="book-footer">
                        <p class="book-price">Rp <?php echo number_format($row['Harga'], 0, ',', '.'); ?></p>
                        <form method="post" action="keranjang.php?action=add" style="display:inline-block; margin:0;">
                            <input type="hidden" name="id_buku" value="<?php echo htmlspecialchars($row['id']); ?>">
                            <input type="hidden" name="jumlah" value="1">
                            <button type="submit" class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i> Add</button>
                        </form>


                    </div>

                </div>

            <?php
                }
            } else {
                echo "<p>Tidak ada buku tersedia.</p>";
            }
            ?>
        </div>
    </div>
</div>


<?php include 'includes/footer.php'; ?>