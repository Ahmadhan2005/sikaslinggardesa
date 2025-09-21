<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// Handle tambah user
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $role = $_POST['role'];

    $query = "INSERT INTO users (nama, username, password, role) 
              VALUES ('$nama', '$username', '$password', '$role')";
    mysqli_query($koneksi, $query);
    header("Location: users.php");
}

// Handle hapus user
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM users WHERE id=$id");
    header("Location: users.php");
}

$users = mysqli_query($koneksi, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pengguna - SiKasLinggar</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header id="header">
    <div class="container">
        <h1 class="logo">SiKasLinggar</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="kas_masuk.php">Kas Masuk</a>
            <a href="kas_keluar.php">Kas Keluar</a>
            <a href="laporan.php">Laporan</a>
            <a href="users.php">Pengguna</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="container" style="margin-top: 100px;">
    <h2>Manajemen Pengguna</h2>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead style="background:#097A48; color:white;">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while ($row = mysqli_fetch_array($users)) : ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $row['nama']; ?></td>
                <td><?= $row['username']; ?></td>
                <td><?= $row['role']; ?></td>
                <td><a href="?hapus=<?= $row['id']; ?>" onclick="return confirm('Hapus pengguna?')">Hapus</a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h3>Tambah Pengguna</h3>
    <form method="POST">
        <input type="text" name="nama" placeholder="Nama Lengkap" required><br><br>
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <select name="role" required>
            <option value="">-- Pilih Role --</option>
            <option value="admin">Admin</option>
            <option value="bendahara">Bendahara</option>
            <option value="warga">Warga</option>
        </select><br><br>
        <button type="submit" name="tambah">Tambah</button>
    </form>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> SiKasLinggar - Desa Linggar</p>
</footer>

<script>
    window.addEventListener('scroll', () => {
        document.getElementById('header').classList.toggle('scrolled', window.scrollY > 20);
    });
</script>
</body>
</html>
