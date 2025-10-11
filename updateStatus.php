<?php
include 'config.php';

if(isset($_POST['id'], $_POST['status'])){
    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE transaksi SET status = ? WHERE id = ?");
    $stmt->bind_param("ss", $status, $id);

    if($stmt->execute()){
        echo "Status berhasil diubah!";
    } else {
        echo "Gagal update status.";
    }

    $stmt->close();
} else {
    echo "Data tidak lengkap!";
}
?>
