<?php
session_start();
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$errors = [];
$success = '';

// profile photo folder and helper
$photoDir = __DIR__ . '/images/fotoprofil';
if (!is_dir($photoDir)) @mkdir($photoDir, 0755, true);

// helper to find existing photo for user id
function find_profile_photo($dir, $id) {
    $candidates = ['jpg','jpeg','png'];
    foreach ($candidates as $ext) {
        $p = $dir . '/' . $id . '.' . $ext;
        if (file_exists($p)) return $p;
    }
    return null;
}

// fetch fresh user data
$stmt = $conn->prepare('SELECT id, Nama, Email, alamat, Nomor_telepon, Role, Password FROM user WHERE id = ? LIMIT 1');
$stmt->bind_param('s', $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc() ?: null;
$stmt->close();

if (!$user) {
    die('User tidak ditemukan.');
}

// handle form posts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // handle photo upload
    if ($action === 'upload_photo') {
        if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Tidak ada file yang diunggah.';
        } else {
            $f = $_FILES['profile_photo'];
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Terjadi kesalahan saat mengunggah.';
            } else {
                // validate size (max 3MB)
                if ($f['size'] > 3 * 1024 * 1024) {
                    $errors[] = 'File terlalu besar (maks 3 MB).';
                } else {
                    // validate type
                    $info = getimagesize($f['tmp_name']);
                    $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png'];
                    if ($info === false || !isset($allowed[$info[2]])) {
                        $errors[] = 'Format gambar tidak didukung. Gunakan JPG atau PNG.';
                    } else {
                        $ext = $allowed[$info[2]];
                        // remove existing profile photos for this user
                        foreach (['jpg','jpeg','png'] as $e) {
                            $old = $photoDir . '/' . $userId . '.' . $e;
                            if (file_exists($old)) @unlink($old);
                        }
                        $dst = $photoDir . '/' . $userId . '.' . $ext;
                        if (move_uploaded_file($f['tmp_name'], $dst)) {
                            // optionally set permissions
                            @chmod($dst, 0644);
                            $success = 'Foto profil berhasil diunggah.';
                        } else {
                            $errors[] = 'Gagal menyimpan file.';
                        }
                    }
                }
            }
        }
    }

    // handle photo delete
    if ($action === 'delete_photo') {
        $deleted = false;
        foreach (['jpg','jpeg','png'] as $e) {
            $p = $photoDir . '/' . $userId . '.' . $e;
            if (file_exists($p)) {
                if (@unlink($p)) $deleted = true; else $errors[] = 'Gagal menghapus file: ' . basename($p);
            }
        }
        if ($deleted) $success = 'Foto profil dihapus.'; else $success = 'Tidak ada foto profil untuk dihapus.';
    }

    if ($action === 'update_profile') {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $telp = trim($_POST['nomor_telepon'] ?? '');

        if ($nama === '') $errors[] = 'Nama tidak boleh kosong.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
        if ($telp !== '' && !preg_match('/^[0-9]{7,15}$/', $telp)) $errors[] = 'Nomor telepon tidak valid (7-15 digit).';

        if (empty($errors)) {
            $stmt = $conn->prepare('UPDATE user SET Nama = ?, Email = ?, alamat = ?, Nomor_telepon = ? WHERE id = ?');
            $stmt->bind_param('sssss', $nama, $email, $alamat, $telp, $userId);
            if ($stmt->execute()) {
                $success = 'Profil berhasil diperbarui.';
                // refresh $user values
                $user['Nama'] = $nama;
                $user['Email'] = $email;
                $user['alamat'] = $alamat;
                $user['Nomor_telepon'] = $telp;
                // update session display name if needed
                $_SESSION['user']['Nama'] = $nama;
            } else {
                $errors[] = 'Gagal menyimpan perubahan: ' . $stmt->error;
            }
            $stmt->close();
        }
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $conf = $_POST['new_password_confirm'] ?? '';

        if ($current === '' || $new === '' || $conf === '') {
            $errors[] = 'Isi semua kolom password.';
        } elseif ($new !== $conf) {
            $errors[] = 'Password baru dan konfirmasi tidak cocok.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'Password baru minimal 8 karakter.';
        } else {
            // verify current password against stored value
            $stored = $user['Password'] ?? '';

            $ok = false;
            // heuristik: jika panjang 32 kemungkinan md5, kalau lain mungkin password_hash
            if (strlen($stored) === 32) {
                if (md5($current) === $stored) $ok = true;
            } else {
                if (password_verify($current, $stored)) $ok = true;
            }

            if (!$ok) {
                $errors[] = 'Password saat ini salah.';
            } else {
                // NOTE: project currently uses md5 in some places. To remain compatible we update using md5.
                // Recommended: migrate to password_hash() later and update login logic.
                $newHash = md5($new);

                $stmt = $conn->prepare('UPDATE user SET Password = ? WHERE id = ?');
                $stmt->bind_param('ss', $newHash, $userId);
                if ($stmt->execute()) {
                    $success = 'Password berhasil diubah.';
                    $user['Password'] = $newHash;
                } else {
                    $errors[] = 'Gagal mengubah password: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>
<?php include 'includes/headerPembeli.php'; ?>

<style>
.profile-wrapper { max-width: 980px; margin: 32px auto; background:#fff; padding:24px; border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,0.04); }
.profile-grid { display:flex; gap:30px; align-items:flex-start; flex-wrap:wrap; }
.profile-card { flex:0 0 260px; padding:18px; border-radius:8px; background:#f7fbff; }
.profile-main { flex:1 1 600px; }
.field { margin-bottom:14px; }
.field label { display:block; font-weight:600; margin-bottom:6px; color:#2e3d49; }
.field input[type="text"], .field input[type="email"], .field input[type="password"], .field textarea {
    width:100%; padding:10px 12px; border:1px solid #d6dbe0; border-radius:8px; background:#fff;
}
.btn { display:inline-block; padding:10px 16px; border-radius:8px; background:#2e7d32; color:#fff; border:none; cursor:pointer; }
.btn.secondary { background:#6bb8e6; color:#123; }
.msg-success { background:#e8f5e9; color:#2e7d32; padding:10px; border-radius:6px; margin-bottom:12px; }
.msg-error { background:#fff5f5; color:#c62828; padding:10px; border-radius:6px; margin-bottom:12px; }
.small-note { font-size:0.9em; color:#666; margin-top:6px; }
</style>

<div class="profile-wrapper">
    <h2>Profil Saya</h2>

    <?php if (!empty($success)): ?>
        <div class="msg-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="msg-error">
            <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
        </div>
    <?php endif; ?>

    <div class="profile-grid">
        <div class="profile-card">
            <?php
                // determine profile photo URL (relative web path)
                $profilePhoto = 'images/fotoprofil/profil-empty.jpg';
                $found = find_profile_photo($photoDir, $userId);
                if ($found) {
                    // convert filesystem path to relative web path
                    $profilePhoto = 'images/fotoprofil/' . rawurlencode(basename($found));
                }
            ?>
            <div style="text-align:center; margin-bottom:10px;">
                <img src="<?php echo $profilePhoto; ?>" alt="Foto profil" style="width:140px; height:140px; object-fit:cover; border-radius:70px; border:3px solid #fff; box-shadow:0 6px 18px rgba(0,0,0,0.08);">
            </div>
            <h3><?php echo htmlspecialchars($user['Nama']); ?></h3>
            <p class="small-note"><strong>Role:</strong> <?php echo htmlspecialchars($user['Role'] ?? 'Pembeli'); ?></p>
            <p class="small-note"><strong>Email:</strong><br><?php echo htmlspecialchars($user['Email']); ?></p>
            <p class="small-note"><strong>Telepon:</strong><br><?php echo htmlspecialchars($user['Nomor_telepon'] ?? '-'); ?></p>
            <p class="small-note"><strong>Alamat:</strong><br><?php echo nl2br(htmlspecialchars($user['alamat'] ?? '-')); ?></p>
            <div style="margin-top:12px;">
                <form method="post" enctype="multipart/form-data" style="display:flex; gap:8px; align-items:center; justify-content:center;">
                    <input type="hidden" name="action" value="upload_photo">
                    <input type="file" name="profile_photo" accept="image/jpeg,image/png" required>
                    <button type="submit" class="btn">Unggah</button>
                </form>
                <form method="post" style="display:flex; gap:8px; align-items:center; justify-content:center; margin-top:8px;">
                    <input type="hidden" name="action" value="delete_photo">
                    <button type="submit" class="btn secondary" onclick="return confirm('Hapus foto profil Anda?');">Hapus Foto</button>
                </form>
            </div>
        </div>

        <div class="profile-main">
            <h3>Update Profil</h3>
            <form method="post" style="margin-bottom:20px;">
                <input type="hidden" name="action" value="update_profile">
                <div class="field">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['Nama']); ?>" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                </div>
                <div class="field">
                    <label for="nomor_telepon">Nomor Telepon</label>
                    <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?php echo htmlspecialchars($user['Nomor_telepon']); ?>">
                </div>
                <div class="field">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                </div>

                <button class="btn" type="submit">Simpan Perubahan</button>
            </form>

            <h3>Ganti Password</h3>
            <form method="post">
                <input type="hidden" name="action" value="change_password">
                <div class="field">
                    <label for="current_password">Password Saat Ini</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="field">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="field">
                    <label for="new_password_confirm">Konfirmasi Password Baru</label>
                    <input type="password" id="new_password_confirm" name="new_password_confirm" required>
                </div>
                <button type="submit" class="btn secondary">Ubah Password</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
