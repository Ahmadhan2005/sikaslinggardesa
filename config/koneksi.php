<?php
// File: config/koneksi.php
// Koneksi database utama untuk seluruh aplikasi

// Load konfigurasi
$config = require __DIR__ . '/database.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting untuk development
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Buat koneksi
    $koneksi = mysqli_connect(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database'],
        $config['port']
    );

    if (!$koneksi) {
        throw new Exception("Koneksi database gagal: " . mysqli_connect_error());
    }

    // Set charset
    mysqli_set_charset($koneksi, $config['charset']);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// ====================================
// HELPER FUNCTIONS
// ====================================

// Fungsi query dengan error handling
function query($sql) {
    global $koneksi;
    $result = mysqli_query($koneksi, $sql);
    if (!$result) {
        throw new Exception("Query error: " . mysqli_error($koneksi));
    }
    return $result;
}

// Fungsi escape string
function escape($string) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, $string);
}

// Fungsi format rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Fungsi format tanggal Indonesia
function formatTanggalIndo($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// ====================================
// DONASI ID GENERATOR FUNCTIONS
// ====================================

// Fungsi untuk generate ID donasi dengan format 001/Sikas/2025
function generateDonasiId($id, $created_at = null) {
    // Jika created_at tidak diberikan, gunakan tahun sekarang
    $year = $created_at ? date('Y', strtotime($created_at)) : date('Y');
    
    // Format ID dengan 3 digit (001, 002, dst)
    $formatted_id = str_pad($id, 3, '0', STR_PAD_LEFT);
    
    // Return format: 001/Sikas/2025
    return $formatted_id . '/SIKAS/' . $year;
}

// Fungsi untuk mendapatkan ID donasi formatted berdasarkan database ID
function getFormattedDonasiId($koneksi, $donasi_id) {
    $donasi_id = (int)$donasi_id;
    $query = "SELECT id, created_at FROM donasi WHERE id = $donasi_id";
    $result = mysqli_query($koneksi, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return generateDonasiId($row['id'], $row['created_at']);
    }
    
    return null;
}

// Fungsi untuk generate ID donasi baru (untuk insert)
function generateNewDonasiId($koneksi) {
    // Get last inserted ID + 1
    $query = "SELECT MAX(id) as max_id FROM donasi";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    
    $next_id = $row['max_id'] ? $row['max_id'] + 1 : 1;
    
    return generateDonasiId($next_id);
}

// Fungsi validasi input
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi cek role admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Fungsi redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi set flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

// Fungsi get flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'];
        $message = $_SESSION['flash_message'];
        
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        
        return array('type' => $type, 'message' => $message);
    }
    return null;
}

// Fungsi upload file
function uploadFile($file, $target_dir = "uploads/", $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf')) {
    // Buat direktori jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = basename($file["name"]);
    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Generate nama file unik
    $new_file_name = uniqid() . '_' . time() . '.' . $file_type;
    $target_file = $target_dir . $new_file_name;
    
    // Cek tipe file
    if (!in_array($file_type, $allowed_types)) {
        return array('success' => false, 'message' => 'Tipe file tidak diizinkan');
    }
    
    // Cek ukuran file (max 5MB)
    if ($file["size"] > 5000000) {
        return array('success' => false, 'message' => 'Ukuran file terlalu besar (max 5MB)');
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return array('success' => true, 'filename' => $new_file_name, 'filepath' => $target_file);
    } else {
        return array('success' => false, 'message' => 'Gagal upload file');
    }
}

// Fungsi menghitung total donasi
function getTotalDonasi($koneksi, $status = 'verified') {
    $status = escape($status);
    $query = "SELECT SUM(jumlah) as total FROM donasi WHERE status = '$status'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ? $row['total'] : 0;
}

// Fungsi menghitung jumlah donatur
function getJumlahDonatur($koneksi, $status = 'verified') {
    $status = escape($status);
    $query = "SELECT COUNT(DISTINCT nama_donatur) as jumlah FROM donasi WHERE status = '$status'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['jumlah'];
}

// Fungsi log aktivitas
function logActivity($koneksi, $user_id, $activity, $description = '') {
    $user_id = (int)$user_id;
    $activity = escape($activity);
    $description = escape($description);
    
    $query = "INSERT INTO activity_log (user_id, activity, description, created_at) 
              VALUES ('$user_id', '$activity', '$description', NOW())";
    mysqli_query($koneksi, $query);
}

// Fungsi untuk mendapatkan data user
function getUserById($koneksi, $user_id) {
    $user_id = (int)$user_id;
    $query = "SELECT * FROM users WHERE id = $user_id";
    $result = mysqli_query($koneksi, $query);
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk cek email exists
function emailExists($koneksi, $email) {
    $email = escape($email);
    $query = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);
    return mysqli_num_rows($result) > 0;
}
?>