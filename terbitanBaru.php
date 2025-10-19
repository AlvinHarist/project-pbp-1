<?php
// 1. Hubungkan ke database
include 'config.php';

// SVG Ikon untuk Star 
function icon_star($class_name) {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $class_name . '"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
}

// SVG Ikon untuk ShoppingCart 
function icon_cart($class_name) {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $class_name . '"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
}

// 2. Ambil Data 
$total_buku_result = mysqli_query($conn, "SELECT COUNT(id) as total_buku FROM buku");
$total_buku = mysqli_fetch_assoc($total_buku_result)['total_buku'];

$total_pelanggan_result = mysqli_query($conn, "SELECT COUNT(id) as total_pelanggan FROM user WHERE Role = 'Pembeli'");
$total_pelanggan = mysqli_fetch_assoc($total_pelanggan_result)['total_pelanggan'];

// Menghitung jumlah penulis unik
$total_penulis_result = mysqli_query($conn, "SELECT COUNT(DISTINCT Penulis) as total_penulis FROM buku");
$total_penulis = mysqli_fetch_assoc($total_penulis_result)['total_penulis'];

include 'includes/header2.php';


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terbitan Baru - Toko Buku</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/terbitanBaru.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>
<body class="page-terbitan-baru">

    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    Buka Buku,<br>
                    Buka Jendela Dunia
                </h1>
                <p class="hero-subtitle">
                    Toko buku pilihanmu! Jelajahi rak-rak buku terbaru
                </p>
                <div class="hero-buttons">
                    <a href="kategori.php" class="btn btn-primary">Jelajahi Koleksi</a>
                    <a href="terbitanBaru.php" class="btn btn-outline">Terbitan Terbaru</a>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div>
                    <div class="stat-number"><?php echo $total_buku; ?></div>
                    <p class="stat-label">Buku Tersedia</p>
                </div>
                <div>
                    <div class="stat-number"><?php echo $total_pelanggan; ?></div>
                    <p class="stat-label">Pelanggan</p>
                </div>
                <div>
                    <div class="stat-number"><?php echo $total_penulis; ?></div>
                    <p class="stat-label">Penulis</p>
                </div>
            </div>
        </div>
    </section>

    <section class="filter-section">
        <div class="container">
            <div class="filter-wrapper">
                <div>
                    <h2 class="section-title">Terbitan Baru</h2>
                </div>
            </div>
        </div>
    </section>

    <section class="books-grid-section">
        <div class="container">
            <div class="books-grid">

                <?php
                $sql_buku = "
                    SELECT 
                        b.*, 
                        AVG(r.Rating) as avg_rating, 
                        COUNT(r.ID_Review) as total_reviews
                    FROM 
                        buku b
                    LEFT JOIN 
                        review r ON b.id = r.ID_Buku COLLATE utf8mb4_unicode_ci
                    GROUP BY 
                        b.id
                    ORDER BY 
                        b.Tanggal_Masuk DESC 
                    LIMIT 12
                ";

                $result_buku = mysqli_query($conn, $sql_buku);

                if (mysqli_num_rows($result_buku) > 0):
                    while ($book = mysqli_fetch_assoc($result_buku)):

                        // 4. Logika Gambar
                        $book_ext = '.jpg'; 
                        $fallback_ext = '.png';
                        $image_path = 'images/' . htmlspecialchars($book['id']) . $book_ext;
                        $fallback_path = 'images/empty' . $fallback_ext; 

                        $image_src = file_exists($image_path) ? $image_path : $fallback_path;
                        
                        // 5. Format Harga
                        $harga_formatted = 'Rp ' . number_format($book['Harga'], 0, ',', '.');
                        
                        // Data untuk rating
                        $rating = floor($book['avg_rating'] ?? 0); 
                        $reviews_count = $book['total_reviews'];

                ?>

                    <div class="book-card">
                        <div class="book-card-image-wrapper">
                            <div class="book-card-image-inner">
                                <img
                                    src="<?php echo $image_src; ?>"
                                    alt="<?php echo htmlspecialchars($book['Judul']); ?>"
                                    class="book-card-image"
                                >
                            </div>
                        </div>
                        
                        <div class="book-card-content">
                            <h3 class="book-card-title"><?php echo htmlspecialchars($book['Judul']); ?></h3>
                            <p class="book-card-author">by <?php echo htmlspecialchars($book['Penulis']); ?></p>
                            
                            <div class="book-card-rating">
                                <?php 
                                for ($i = 0; $i < 5; $i++): 
                                    if ($i < $rating):
                                        echo icon_star('star star-filled');
                                    else:
                                        echo icon_star('star star-empty');
                                    endif;
                                endfor; 
                                ?>
                                <span class="book-card-reviews">(<?php echo $reviews_count; ?>)</span>
                            </div>
                            
                            <div class="book-card-footer">
                                <p class="book-card-price"><?php echo $harga_formatted; ?></p>
                                
                                <?php if (isset($_SESSION['user'])): // Cek apakah user sudah login ?>
                                    
                                    <form action="keranjang.php" method="POST" style="margin: 0;">
                                        <input type="hidden" name="action" value="add">
                                        
                                        <input type="hidden" name="id_buku" value="<?php echo htmlspecialchars($book['id']); ?>">
                                        
                                        <input type="hidden" name="jumlah" value="1">
                                        
                                        <button type="submit" class="btn btn-primary btn-add-cart">
                                            <?php echo icon_cart('icon-cart'); ?>
                                            Tambah
                                        </button>
                                    </form>
                                    
                                <?php else:  ?>
                                    
                                    <a href="login.php" class="btn btn-primary btn-add-cart">
                                        <?php echo icon_cart('icon-cart'); ?>
                                        Login & Tambah
                                    </a>

                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    endwhile;
                else:
                    echo "<p>Belum ada buku terbitan baru.</p>";
                endif;
                ?>

            </div>
        </div>
    </section>

</body>
</html>