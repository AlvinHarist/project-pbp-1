<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// 1. Hubungkan ke database
include 'config.php';


// SVG Ikon untuk Star (Bintang)
function icon_star($class_name) {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $class_name . '"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
}

// SVG Ikon untuk ShoppingCart (Keranjang Belanja)
function icon_cart($class_name) {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' . $class_name . '"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
}

// 2. Query mengambil semua kategori
$kategori_list = [];
$kategori_result = mysqli_query($conn, "SELECT id, Nama_Kategori FROM kategori ORDER BY Nama_Kategori ASC");
if ($kategori_result) {
    while ($row = mysqli_fetch_assoc($kategori_result)) {
        $kategori_list[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Buku - BukaBuku</title>
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="css/kategori.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>
<body class="page-kategori">

    <?php 
    include 'includes/header.php'; 
    ?>

    <div class="page-header">
        <div class="container">
            <h1>Jelajahi Berdasarkan Kategori</h1>
            <p>
                Temukan buku dari berbagai genre dan dapatkan bacaan favoritmu sekarang
            </p>
        </div>
    </div>

    <?php 
    $section_index = 0; 
    foreach ($kategori_list as $kategori): 
        $kategori_id = $kategori['id'];
        $kategori_nama = $kategori['Nama_Kategori'];
        
        $bg_class = ($section_index % 2 == 0) ? 'bg-white' : 'bg-gradient';
        $section_index++;
    ?>
    
    <section class="category-section <?php echo $bg_class; ?>">
        <div class="container">
            <div class="category-section-header">
                <h2><?php echo htmlspecialchars($kategori_nama); ?></h2>
            </div>

            <div class="books-carousel-container">
                <div class="books-carousel-content">
                    
                    <?php
                    $sql_buku = "
                        SELECT 
                            b.id, b.Judul, b.Penulis, b.Harga,
                            AVG(r.Rating) as avg_rating
                        FROM 
                            buku b
                        LEFT JOIN 
                            review r ON b.id = r.ID_Buku COLLATE utf8mb4_unicode_ci
                        WHERE 
                            b.ID_Kategori = ?
                        GROUP BY 
                            b.id
                        ORDER BY 
                            b.Tanggal_Masuk DESC 
                        LIMIT 10
                    ";
                    
                    $stmt = $conn->prepare($sql_buku);
                    $stmt->bind_param("i", $kategori_id);
                    $stmt->execute();
                    $result_buku = $stmt->get_result();

                    if ($result_buku->num_rows > 0):
                        while ($book = $result_buku->fetch_assoc()):
                            
                            // Logika Gambar 
                            $book_ext = '.jpg'; 
                            $fallback_ext = '.png'; 
                            $image_path = 'images/' . htmlspecialchars($book['id']) . $book_ext;
                            $fallback_path = 'images/empty' . $fallback_ext;
                            $image_src = file_exists($image_path) ? $image_path : $fallback_path;
                            
                            // Format Harga
                            $harga_formatted = 'Rp ' . number_format($book['Harga'], 0, ',', '.');
                            $rating = round($book['avg_rating'] ?? 0, 1); 
                    ?>
                    
                    <div class="books-carousel-item">
                        <div class="kategori-book-card">
                            <a href="detailProduk.php?id=<?php echo htmlspecialchars($book['id']); ?>">
                                <div class="kategori-book-card-image-wrapper">
                                    <img
                                        src="<?php echo $image_src; ?>"
                                        alt="<?php echo htmlspecialchars($book['Judul']); ?>"
                                        class="kategori-book-card-image"
                                    >
                                </div>
                            </a>

                            <div class="kategori-book-card-content">
                                <h3 class="kategori-book-card-title">
                                    <a href="detailProduk.php?id=<?php echo htmlspecialchars($book['id']); ?>">
                                        <?php echo htmlspecialchars($book['Judul']); ?>
                                    </a>
                                </h3>
                                <p class="kategori-book-card-author">by <?php echo htmlspecialchars($book['Penulis']); ?></p>
                                
                                <div class="kategori-book-card-footer-wrapper">
                                    <div class="kategori-book-card-rating">
                                        <?php echo icon_star('star'); ?>
                                        <span><?php echo $rating; ?></span>
                                    </div>
                                    
                                    <p class="kategori-book-card-price"><?php echo $harga_formatted; ?></p>
                                    
                                    <?php if (isset($_SESSION['user'])): ?>
                                        <form action="keranjang.php" method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="id_buku" value="<?php echo htmlspecialchars($book['id']); ?>">
                                            <input type="hidden" name="jumlah" value="1">
                                            <button type="submit" class="btn-add-cart">
                                                <?php echo icon_cart('icon-cart'); ?>
                                                Tambah
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="login.php" class="btn-add-cart">
                                            <?php echo icon_cart('icon-cart'); ?>
                                            Tambah
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div> <?php 
                        endwhile;
                    else:
                        echo "<p class='no-books-message'>Belum ada buku di kategori ini.</p>";
                    endif;
                    $stmt->close();
                    ?>

                </div> </div> </div>
    </section>
    
    <?php endforeach; ?>
    <footer class="footer">
        <div class="container">
            <p>© 2025 BukaBuku. Toko buku pelangganmu, selalu ada untuk kamu.</p>
        </div>
    </footer>
    
</body>
</html>