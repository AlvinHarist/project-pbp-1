<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "config.php";
include 'includes/headerPembeli.php';
?>

<?php
// Fungsi ambil buku teratas
function getTopBooks($conn, $limit = 4) {
    $sql = "SELECT * FROM buku ORDER BY Tanggal_masuk DESC LIMIT $limit"; 
    $result = $conn->query($sql);

    $books = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
    return $books;
}

// Ambil 4 buku teratas
$topBooks = getTopBooks($conn, 4);

// Fungsi ambil rating buku
function getBookRating($conn, $idBuku) {
    $sql = "SELECT AVG(Rating) AS avgRating, COUNT(*) AS totalReview FROM review WHERE ID_Buku = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $idBuku);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    $stmt->close();
    $avg = $data['avgRating'];
    if (is_null($avg)) $avg = 0;
    return [
        'avgRating' => round(floatval($avg), 1),
        'totalReview' => intval($data['totalReview'])
    ];
}
$sqlKategori = "
    SELECT k.nama_kategori, COUNT(k.nama_kategori) as totalBuku
    FROM buku b 
    left join kategori k on b.id_kategori = k.id
    GROUP BY k.nama_kategori;
";
$resultKategori = $conn->query($sqlKategori);

$kategoriBuku = [];
if ($resultKategori && $resultKategori->num_rows > 0) {
    while ($row = $resultKategori->fetch_assoc()) {
        $kategoriBuku[] = $row; // setiap elemen berisi ['Kategori' => ..., 'totalBuku' => ...]
    }
}
?>

<?php
// Ambil Book of the Month otomatis berdasarkan rating tertinggi dan review terbanyak
$sqlBotm = "
    SELECT b.id, b.Judul, b.Penulis, b.Harga,
       AVG(r.Rating) AS avgRating,
       COUNT(r.ID_Buku) AS totalReview,
       (AVG(r.Rating) * COUNT(r.ID_Buku)) AS score
    FROM buku b
    LEFT JOIN review r 
        ON b.id COLLATE utf8mb4_general_ci = r.ID_Buku COLLATE utf8mb4_general_ci
    GROUP BY b.id, b.Judul, b.Penulis, b.Harga
    ORDER BY score DESC
    LIMIT 1
";
$resultBotm = $conn->query($sqlBotm);


if ($resultBotm && $resultBotm->num_rows > 0) {
    $botmBook = $resultBotm->fetch_assoc();
    $botmRating = [
        'avgRating' => round($botmBook['avgRating'], 1),
        'totalReview' => $botmBook['totalReview']
    ];
} else {
    // fallback jika belum ada review
    $botmBook = [
        'id' => 'default',
        'Judul' => 'Belum ada Book of the Month'
    ];
    $botmRating = ['avgRating' => 0, 'totalReview' => 0];
}

// Jumlah buku
$sqlBuku = "SELECT COUNT(*) AS totalBuku FROM buku";
$resultBuku = $conn->query($sqlBuku);
$totalBuku = $resultBuku->fetch_assoc()['totalBuku'] ?? 0;

// Jumlah pelanggan
$sqlPelanggan = "SELECT COUNT(*) AS totalPelanggan FROM user where role='pembeli'"; // ganti nama tabel kalau berbeda
$resultPelanggan = $conn->query($sqlPelanggan);
$totalPelanggan = $resultPelanggan->fetch_assoc()['totalPelanggan'] ?? 0;

// Jumlah penulis
$sqlPenulis = "SELECT COUNT(DISTINCT Penulis) AS totalPenulis FROM buku";
$resultPenulis = $conn->query($sqlPenulis);
$totalPenulis = $resultPenulis->fetch_assoc()['totalPenulis'] ?? 0;

$icons = [
    'Fiksi' => 'fa-book-open',
    'Romantis' => 'fa-heart',
    'Thriller' => 'fa-bolt',
    'Fantasi' => 'fa-rocket',
    'Sejarah' => 'fa-landmark',
    'Sains' => 'fa-flask',
    'Seni & Desain' => 'fa-palette',
    'Biografi' => 'fa-user-edit',
    'Gizi' => 'fa-utensils',
    'Komputer' => 'fa-laptop',
    'Majalah' => 'fa-newspaper',
    'Self-development' => 'fa-user'
];

$allCategories = [
    'Fiksi', 'Romantis', 'Thriller', 'Fantasi', 
    'Sejarah', 'Sains', 'Seni & Desain', 'Biografi'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BukaBuku</title>
  <link rel="stylesheet" href="css/dashboardP.css"> 
</head>

<body>
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Buka Buku, <br>Buka Jendela Dunia</h1>
                    <p>Toko buku pilihanmu! Jelajahi rak-rak buku terpopuler</p>
                    <div class="hero-buttons">
                        <a href="#" class="btn btn-primary">Jelajahi Koleksi</a>
                        <a href="terbitanBaru.php" class="btn btn-secondary">Terbitan Terbaru</a>
                    </div>
                    <div class="stats">
                        <div><strong><?= number_format($totalBuku); ?></strong><span>Buku Tersedia</span></div>
                        <div><strong><?= number_format($totalPelanggan); ?></strong><span>Pelanggan</span></div>
                        <div><strong><?= number_format($totalPenulis); ?></strong><span>Penulis</span></div>
                    </div>
                </div>

                <div class="hero-image">
                    <img src="images/<?= $botmBook['id']; ?>.jpg" alt="<?= $botmBook['Judul']; ?>">
                    <div class="book-of-the-month">
                        <p>Book of the Month</p>
                        <strong><?= $botmBook['Judul']; ?></strong>
                        <span><i class="fas fa-star"></i> <?= $botmRating['avgRating']; ?> (<?= $botmRating['totalReview']; ?>)</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

<section class="Kategori">
    <div class="container">
        <div class="section-title">
            <h2>Jelajahi Berdasarkan Kategori</h2>
            <p>Temukan buku dari berbagai genre dan dapatkan bacaan favoritmu berikutnya</p>
        </div>
        <div class="category-grid">
            <?php
            // Semua kategori yang ingin ditampilkan
            $allCategories = [
                'Fiksi', 'Romantis', 'Thriller', 'Fantasi',
                'Sejarah', 'Sains', 'Seni & Desain', 'Biografi',
                'Gizi', 'Komputer', 'Majalah', 'Self-development'
            ];

            foreach($allCategories as $kategori) {
                $total = 0;
                // cari jumlah buku dari hasil query database
                foreach($kategoriBuku as $kb){
                    if($kb['nama_kategori'] == $kategori){
                        $total = $kb['totalBuku'];
                        break;
                    }
                }
            ?>
                <div class="category-card">
                    <i class="fas <?= $icons[$kategori] ?? 'fa-book' ?>"></i>
                    <h3><?= $kategori ?></h3>
                    <span><?= number_format($total) ?> buku</span>
                </div>
            <?php } ?>
        </div>
    </div>
</section>


<section class="rekomendasi">
    <div class="title-row">
        <h2>Rekomendasi Buku</h2>
        <h3><a href="rekomendasi.php" class="selengkapnya-link">Selengkapnya</a></h3>
    </div>
    <div class="grid">
        <?php foreach($topBooks as $buku): ?>
            <?php $rating = getBookRating($conn, $buku['id']); ?>
            <a class="book-card-link" href="detailproduk.php?id=<?php echo urlencode($buku['id']); ?>">
            <div class="book-card" data-id="<?php echo htmlspecialchars($buku['id']); ?>">
                <div class="book-image-container">
                    <img src="images/<?php echo $buku['id']; ?>.jpg" alt="<?php echo $buku['Judul']; ?>" loading="lazy">
                </div>
                <div class="book-info">
                    <h3><?php echo $buku['Judul']; ?></h3>
                    <p>by <?php echo $buku['Penulis']; ?></p>
                    <p class="book-price">Rp <?= number_format($buku['Harga'], 0, ',', '.') ?></p>
                    <div class="book-rating">
                        <i class="fas fa-star"></i> <?= $rating['avgRating']; ?> (<?= $rating['totalReview']; ?>)
                    </div>
                    <button class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i> Tambah</button>
                </div>
            </div>
            </a>
        <?php endforeach; ?>

    </div>

</section>

</body>
</html>
