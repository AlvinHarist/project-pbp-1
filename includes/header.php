<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BukaBuku</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body data-logged-in="<?php echo !empty($_SESSION['user']) ? '1' : '0'; ?>">

<header>
    <div class="container">
        <nav>
            <a href="http://localhost/Book-Store/index.php" class="logo">BukaBuku</a>
            <ul>
                <li><a href="kategori.php">Kategori</a></li>
                <li><a href="#">Bestsellers</a></li>
                <li><a href="#" class="active">Terbitan Baru</a></li>
                <li><a href="#">About</a></li>
            </ul>
            <div class="nav-right">
                <form action="search.php" method="get" class="search-box" role="search">
                    <label for="q" class="sr-only">Cari</label>
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input id="q" name="q" type="text" placeholder="Cari buku, penulis, genre..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';?>">
                    <button type="submit" class="search-submit" aria-label="Cari"><i class="fas fa-arrow-right"></i></button>
                </form>
                <div class="icons">
                    <a href="#"><i class="fas fa-heart"></i></a>
                    <a href="keranjang.php"><i class="fas fa-shopping-cart"></i></a>
                    <?php if (!empty($_SESSION['user'])): ?>
                        <a href="profile.php"><i class="fas fa-user"></i></a>
                        <a href="logout.php" class='masuk-icon'>Keluar</a>
                    <?php else: ?>
                        <a href="login.php" class='masuk-icon'>Masuk</a>
                        <a href="signup.php" class='daftar-icon'>Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</header>

<main>