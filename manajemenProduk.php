<?php
include 'config.php';
include 'includes/headerAdmin.php'; 

// Ganti dengan detail koneksi database Anda
$servername = "localhost";
$username = "root"; // Sesuaikan jika berbeda
$password = ""; // Sesuaikan jika berbeda
$dbname = "toko_buku"; // Sesuai dengan nama database Anda

// 1. KONEKSI KE DATABASE
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    // Tampilkan pesan error yang lebih aman (opsional)
    die("Koneksi ke database gagal.");
    // Atau tampilkan detail error untuk development: die("Koneksi gagal: " . $conn->connect_error);
}

$edit_mode = false;
$book_to_edit = null;

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $book_id = $conn->real_escape_string($_GET['id']);
    
    // Perintah DELETE SQL
    $delete_sql = "DELETE FROM buku WHERE id = '$book_id'";
    
    if ($conn->query($delete_sql) === TRUE) {
        // Redirect kembali ke halaman ini dengan status sukses
        header("Location: manajemenProduk.php?status=deleted");
        exit();
    } else {
        // Handle error
        $error_message = "Error menghapus data: " . $conn->error;
    }
}
// --- AKHIR LOGIKA HAPUS DATA ---

// --- LOGIKA FORM INSERT DATA DENGAN VALIDASI DUPLIKAT DI PHP ---

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_product') {
    // 1. Ambil dan sanitasi data dari form
    $judul = trim($_POST['title']);
    $penulis = trim($_POST['author']);
    $harga = (float)$_POST['price'];
    $id_kategori = (int)$_POST['category'];

    // 2. CEK DUPLIKASI BUKU DENGAN QUERY SELECT
    // Memeriksa apakah kombinasi Judul dan Penulis sudah ada
    $check_sql = "SELECT id FROM buku WHERE Judul = ? AND Penulis = ?";
    $stmt_check = $conn->prepare($check_sql);
    
    if (!$stmt_check) {
        // Penanganan jika prepare gagal 
        header("Location: manajemenProduk.php?status=error&message=Gagal menyiapkan check duplikat: " . urlencode($conn->error));
        exit();
    }
    
    $stmt_check->bind_param("ss", $judul, $penulis);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        header("Location: manajemenProduk.php?status=error&message=Gagal: Buku dengan Judul dan Penulis yang sama sudah ada di katalog.");
        $stmt_check->close();
        exit();
    }
    $stmt_check->close(); // Tutup statement check

    // 3. JIKA TIDAK DUPLIKAT, LANJUTKAN PROSES INSERT
    $id_buku = 'B' . uniqid(); 
    $penerbit = "Gramedia Pustaka Utama"; 
    $tahun = date("Y"); 
    $stok = 50; 
    
    // 4. Query INSERT menggunakan Prepared Statement
    $insert_sql = "INSERT INTO buku (id, Judul, Penulis, Penerbit, Tahun, Harga, Stok, ID_Kategori) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_insert = $conn->prepare($insert_sql);
    
    if (!$stmt_insert) {
        // Penanganan jika prepare insert gagal
        header("Location: manajemenProduk.php?status=error&message=Gagal menyiapkan insert data: " . urlencode($conn->error));
        exit();
    }

    $stmt_insert->bind_param("sssssdis", $id_buku, $judul, $penulis, $penerbit, $tahun, $harga, $stok, $id_kategori);
    
    if ($stmt_insert->execute()) {
        header("Location: manajemenProduk.php?status=success&message=Produk berhasil ditambahkan!");
        $stmt_insert->close();
        exit();
    } else {
        // Tangani error insert
        header("Location: manajemenProduk.php?status=error&message=Gagal menyimpan data: " . urlencode($stmt_insert->error));
        $stmt_insert->close();
        exit();
    }
}
// --- AKHIR LOGIKA FORM INSERT DATA DENGAN VALIDASI DUPLIKAT DI PHP ---

// --- LOGIKA FORM UPDATE DATA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_product') {
    // 1. Ambil dan sanitasi data dari form
    $book_id = $conn->real_escape_string($_POST['book_id']); // ID buku yang diedit
    $judul = $conn->real_escape_string($_POST['title']);
    $penulis = $conn->real_escape_string($_POST['author']);
    $harga = (float)$_POST['price'];
    $id_kategori = (int)$_POST['category'];
    //$edit_mode = false;
    //$book_to_edit = null;


    // 2. Query UPDATE
    $update_sql = "UPDATE buku SET 
                   Judul = '$judul', 
                   Penulis = '$penulis', 
                   Harga = $harga, 
                   ID_Kategori = $id_kategori
                   WHERE id = '$book_id'";
    
    if ($conn->query($update_sql) === TRUE) {
        header("Location: manajemenProduk.php?status=updated");
        exit();
    } else {
        $error_message = "Error: " . $update_sql . "<br>" . $conn->error;
    }
}
// --- AKHIR LOGIKA FORM UPDATE DATA ---

// --- LOGIKA AMBIL DATA BUKU YANG AKAN DIEDIT ---
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_mode = true;
    $book_id_edit = $conn->real_escape_string($_GET['id']);
    
    $edit_sql = "SELECT * FROM buku WHERE id = '$book_id_edit'";
    $edit_result = $conn->query($edit_sql);
    
    if ($edit_result->num_rows == 1) {
        $book_to_edit = $edit_result->fetch_assoc();
    } else {
        // Jika ID tidak ditemukan, kembali ke tampilan normal
        header("Location: manajemenProduk.php");
        exit();
    }
}
// --- AKHIR LOGIKA AMBIL DATA BUKU ---


// --- LOGIKA QUERY DATA BUKU YANG AKAN DITAMPILKAN DI TABEL ---

// 1. Mengambil semua Kategori dari database (untuk dropdown form)
$category_sql = "SELECT id, Nama_Kategori FROM kategori ORDER BY Nama_Kategori ASC";
$category_result = $conn->query($category_sql);

$categories = [];
if ($category_result->num_rows > 0) {
    while($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Definisikan path dan fallback image
$cover_folder = "images/"; 
$fallback_image = $cover_folder . "empty.png"; 

// 2. Query untuk mengambil semua DATA BUKU (untuk tabel)
$sql = "
    SELECT
        b.id,
        b.Judul,
        b.Penulis,
        b.Harga,
        b.Stok,
        k.Nama_Kategori AS Category,
        COALESCE(AVG(r.Rating), 0) AS Rating,
        COALESCE(COUNT(r.ID_Review), 0) AS Reviews
    FROM
        buku b
    LEFT JOIN
        kategori k ON b.ID_Kategori = k.id
    LEFT JOIN
        review r ON b.id = r.ID_Buku COLLATE utf8mb4_general_ci
    GROUP BY
        b.id, b.Judul, b.Penulis, b.Harga, b.Stok, k.Nama_Kategori
    ORDER BY
        b.Judul ASC;
";
$result = $conn->query($sql);

$products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$total_products = count($products);

function get_image_url($book_id, $folder, $fallback) {
    $extensions = ['jpg', 'png', 'jpeg'];
    $book_id = htmlspecialchars($book_id);

    foreach ($extensions as $ext) {
        $filename = $book_id . '.' . $ext;
        $file_path_relative = $folder . $filename;
        $file_path_absolute = __DIR__ . '/' . $file_path_relative;

        if (file_exists($file_path_absolute)) {
            return $file_path_relative;
        }
    }
    return $fallback;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - BukaBuku</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Covered+By+Your+Grace&family=Poppins:wght@700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/manajemenProduk.css"> 
</head>
<body> <!--
    <div class="mp-container">
        <div class="mp-header">
            <div class="mp-avatar-container">
                <div class="mp-avatar">
                    <svg class="mp-person-svg" fill="none" preserveAspectRatio="none" viewBox="0 0 38 36">
                        <g id="Person">
                            <path d="M 19 0 C 12.396 0 7 5.396 7 12 C 7 18.604 12.396 24 19 24 C 25.604 24 31 18.604 31 12 C 31 5.396 25.604 0 19 0 Z M 31 29 C 31 30.65 30.34 32.184 29.176 33.351 C 27.994 34.529 26.467 35.2 24.811 35.2 L 13.189 35.2 C 11.533 35.2 10.006 34.529 8.824 33.351 C 7.66 32.184 7 30.65 7 29 L 7 28 C 7 26.34 7.66 24.816 8.824 23.649 C 10.006 22.471 11.533 21.8 13.189 21.8 L 24.811 21.8 C 26.467 21.8 27.994 22.471 29.176 23.649 C 30.34 24.816 31 26.34 31 28 L 31 29 Z" fill="var(--fill-0, white)" id="shape" />
                        </g>
                    </svg>
                </div>
            </div>
            <p class="mp-header-title">BukaBuku</p>
            <p class="mp-header-subtitle">BukaBuku</p>
            <a href="dashboardAdmin.php" class="mp-back-button">
                <svg class="mp-icon-sm" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                Kembali ke Dashboard
            </a>
        </div>-->
        
        <div class="mp-main-content-wrapper">
            <div class="mp-hero-section">
                <h1 class="mp-hero-title">Manajemen Produk</h1>
                <p class="mp-hero-subtitle">Kelola katalog buku BukaBuku Anda</p>
            </div>

            <div class="mp-action-area">
                <button id="toggle-form-btn" class="mp-add-product-btn">
                    <svg class="mp-icon-md" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Tambah Produk Baru
                </button>
            </div>

            <div id="product-form-container" class="mp-form-wrapper" style="display: <?php echo $edit_mode ? 'block' : 'none'; ?>;"> 
                <div class="mp-form-card">
                    <h3 id="form-title" class="mp-form-title"><?php echo $edit_mode ? 'Edit Buku' : 'Tambah Buku Baru'; ?></h3>
                    <button type="submit" id="submit-form-btn" class="mp-submit-btn">
                        <?php echo $edit_mode ? 'Update Produk' : 'Simpan Produk'; ?>
                    </button>

                    <form action="manajemenProduk.php" method="POST" class="mp-product-form">
                        
                        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update_product' : 'add_product'; ?>">
                        
                        <?php if ($edit_mode): ?>
                            <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($book_to_edit['id']); ?>">
                        <?php endif; ?>
                        
                        <div>
                            <label for="title" class="mp-form-label">Judul Buku</label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title" 
                                required 
                                class="mp-form-input"
                                value="<?php echo $edit_mode ? htmlspecialchars($book_to_edit['Judul']) : ''; ?>"
                            > 
                        </div>
                        <div>
                            <label for="author" class="mp-form-label">Penulis</label>
                            <input 
                                type="text" 
                                id="author" 
                                name="author" 
                                required 
                                class="mp-form-input"
                                value="<?php echo $edit_mode ? htmlspecialchars($book_to_edit['Penulis']) : ''; ?>"
                            >
                        </div>
                        <div>
                            <label for="price" class="mp-form-label">Harga (Rp)</label>
                            <input 
                                type="number" 
                                id="price" 
                                name="price" 
                                required 
                                class="mp-form-input"
                                value="<?php echo $edit_mode ? htmlspecialchars($book_to_edit['Harga']) : ''; ?>"
                            >
                        </div>
                        
                        <div>
                            <label for="category" class="mp-form-label">Kategori</label>
                            <select id="category" name="category" class="mp-form-input">
                                <option value="">-- Pilih Kategori --</option>
                                <?php
                                if (!empty($categories)) {
                                    foreach ($categories as $category) {
                                        $selected = '';
                                        if ($edit_mode && $category['id'] == $book_to_edit['ID_Kategori']) {
                                            $selected = 'selected';
                                        }
                                        echo "<option value='" . htmlspecialchars($category['id']) . "' {$selected}>" . htmlspecialchars($category['Nama_Kategori']) . "</option>";
                                    }
                                }
                                ?>
                            </select> 
                        </div>
                        <div class="mp-col-span-2">
                            <label for="cover-image" class="mp-form-label">Upload Gambar Cover</label>
                            <div class="mp-image-upload-area">
                                <input type="file" id="cover-image" name="cover-image" accept="image/*" class="mp-file-input">
                            </div>
                            <p class="mp-input-hint">Format yang didukung: JPG, PNG, GIF (Max 5MB)</p>
                        </div>
                        <div class="mp-col-span-2 mp-form-actions">
                            <button type="button" id="cancel-form-btn" class="mp-cancel-btn">
                                Batal
                            </button>
                            <button type="submit" id="submit-form-btn" class="mp-submit-btn">
                                <?php echo $edit_mode ? 'Update Produk' : 'Simpan Produk'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mp-table-area">
                <h3 class="mp-table-title">Daftar Produk (<?php echo $total_products; ?>)</h3>
                <div class="mp-table-card">
                    <table class="mp-product-table">
                        <thead class="mp-table-head-bg">
                            <tr>
                                <th class="mp-table-header">Cover</th>
                                <th class="mp-table-header">Judul</th>
                                <th class="mp-table-header">Penulis</th>
                                <th class="mp-table-header">Kategori</th>
                                <th class="mp-table-header">Harga</th>
                                <th class="mp-table-header">Rating</th>
                                <th class="mp-table-header mp-text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($total_products > 0): 
                                $row_count = 0;
                                foreach ($products as $product): 
                                    $row_count++;
                                    // Tentukan class untuk ganjil/genap
                                    $row_class = ($row_count % 2 == 0) ? 'mp-table-row-even' : 'mp-table-row-odd';

                                    // LOGIKA PENANGANAN GAMBAR
                                    $image_url = get_image_url($product['id'], $cover_folder, $fallback_image);
                                    
                                    // Format Rating dan Reviews
                                    $display_rating = number_format($product['Rating'], 1);
                                    $display_reviews = number_format($product['Reviews'], 0, ',', '.');
                            ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td class="mp-table-data-cover">
                                    <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($product['Judul']); ?>" class="mp-cover-image">
                                </td>
                                <td class="mp-table-data-title">
                                    <?php echo htmlspecialchars($product['Judul']); ?>
                                </td>
                                <td class="mp-table-data-author">
                                    <?php echo htmlspecialchars($product['Penulis']); ?>
                                </td>
                                <td class="mp-table-data-category">
                                    <span class="mp-category-badge">
                                        <?php echo htmlspecialchars($product['Category']); ?>
                                    </span>
                                </td>
                                <td class="mp-table-data-price">
                                    <?php echo 'Rp. ' . number_format($product['Harga'], 0, ',', '.'); ?>
                                </td>
                                <td class="mp-table-data-rating">
                                    <?php echo "⭐ {$display_rating} ({$display_reviews})"; ?>
                                </td>
                                <td class="mp-table-data-actions">
                                    <div class="mp-action-buttons">
                                        <button class="mp-edit-btn" 
                                                title="Edit produk" 
                                                onclick="window.location.href='manajemenProduk.php?action=edit&id=<?php echo htmlspecialchars($product['id']); ?>'"
                                        >
                                                
                                            <svg class="mp-icon-xs" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                        </button>
                                        <button class="mp-delete-btn" 
                                                title="Hapus produk" 
                                                onclick="if(confirm('Apakah Anda yakin ingin menghapus buku <?php echo htmlspecialchars($product['Judul']); ?>?')) { window.location.href='manajemenProduk.php?action=delete&id=<?php echo htmlspecialchars($product['id']); ?>'; }"
                                        >
                                            <svg class="mp-icon-sm" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="7" class="mp-table-data-title" style="text-align: center;">
                                    <p style="padding: 20px;">Belum ada produk dalam database.</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const formContainer = document.getElementById('product-form-container');
        const toggleBtn = document.getElementById('toggle-form-btn');
        const cancelBtn = document.getElementById('cancel-form-btn');

        toggleBtn.addEventListener('click', () => {
            const isVisible = formContainer.style.display !== 'none';
            formContainer.style.display = isVisible ? 'none' : 'block';
            toggleBtn.innerHTML = isVisible 
                ? '<svg class="mp-icon-md" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Tambah Produk Baru' 
                : '<svg class="mp-icon-md" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Tutup Form';
        });

        cancelBtn.addEventListener('click', () => {
            formContainer.style.display = 'none';
            toggleBtn.innerHTML = '<svg class="mp-icon-md" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Tambah Produk Baru';
            document.getElementById('form-title').textContent = 'Tambah Buku Baru';
            document.getElementById('submit-form-btn').textContent = 'Simpan Produk';
            // Logic to clear form fields would go here in a real application
        });
    </script>
</body>
</html>