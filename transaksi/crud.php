<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

// DELETE
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM transaksi WHERE id = $id");
    header("Location: ../dashboard.php");
    exit();
}

// READ (DETAIL)
if (isset($_GET['detail'])) {
    $id = intval($_GET['detail']);
    $query = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id = $id");
    $data = mysqli_fetch_assoc($query);
    echo json_encode($data);
    exit();
}

// UPDATE
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $kategori_id = $_POST['kategori_id'];
    $nominal = $_POST['nominal'];

    $update = "UPDATE transaksi SET tanggal='$tanggal', keterangan='$keterangan', kategori_id='$kategori_id', nominal='$nominal' WHERE id=$id";
    mysqli_query($koneksi, $update);
    header("Location: ../dashboard.php");
    exit();
}

// FORM TAMBAH (CREATE)
if (isset($_POST['tambah'])) {
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $kategori_id = $_POST['kategori_id'];
    $nominal = $_POST['nominal'];

    $insert = "INSERT INTO transaksi (tanggal, keterangan, kategori_id, nominal) VALUES ('$tanggal', '$keterangan', '$kategori_id', '$nominal')";
    mysqli_query($koneksi, $insert);
    header("Location: ../dashboard.php");
    exit();
}
?>
