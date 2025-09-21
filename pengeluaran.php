<?php
session_start();

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include koneksi database
require_once 'config/koneksi.php';

// Function untuk generate ID Transaksi
function generateTransaksiId($id, $tanggal) {
    $tahun = date('Y', strtotime($tanggal));
    return sprintf("%03d/SIKAS/%s", $id, $tahun);
}

// Handle tambah pengeluaran
if(isset($_POST['tambah_pengeluaran'])) {
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $tujuan = mysqli_real_escape_string($koneksi, $_POST['tujuan']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $jumlah = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $user_id = $_SESSION['user_id'];
    
    // Query insert transaksi untuk pengeluaran
    $query = "INSERT INTO transaksi (tanggal, jenis, kategori, sumber, jumlah, keterangan, user_id) 
              VALUES ('$tanggal', 'pengeluaran', '$kategori', '$tujuan', '$jumlah', '$keterangan', '$user_id')";
    
    if(mysqli_query($koneksi, $query)) {
        // Log aktivitas
        $activity = "Menambah pengeluaran untuk $tujuan sebesar Rp " . number_format($jumlah, 0, ',', '.');
        $log_query = "INSERT INTO activity_log (user_id, activity) VALUES ('$user_id', '$activity')";
        mysqli_query($koneksi, $log_query);
        
        $success = "Pengeluaran berhasil ditambahkan!";
    } else {
        $error = "Gagal menambah pengeluaran: " . mysqli_error($koneksi);
    }
}

// Handle edit pengeluaran
if(isset($_POST['edit_pengeluaran'])) {
    $id = mysqli_real_escape_string($koneksi, $_POST['id']);
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $tujuan = mysqli_real_escape_string($koneksi, $_POST['tujuan']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $jumlah = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $user_id = $_SESSION['user_id'];
    
    $query = "UPDATE transaksi SET 
              tanggal = '$tanggal',
              kategori = '$kategori',
              sumber = '$tujuan',
              jumlah = '$jumlah',
              keterangan = '$keterangan'
              WHERE id = '$id' AND jenis = 'pengeluaran'";
    
    if(mysqli_query($koneksi, $query)) {
        // Log aktivitas
        $activity = "Mengubah data pengeluaran ID: $id";
        $log_query = "INSERT INTO activity_log (user_id, activity) VALUES ('$user_id', '$activity')";
        mysqli_query($koneksi, $log_query);
        
        $success = "Pengeluaran berhasil diperbarui!";
    } else {
        $error = "Gagal mengubah pengeluaran: " . mysqli_error($koneksi);
    }
}

// Handle hapus pengeluaran
if(isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $user_id = $_SESSION['user_id'];
    
    // Get data before delete for logging
    $get_query = "SELECT sumber, jumlah FROM transaksi WHERE id = '$id' AND jenis = 'pengeluaran'";
    $get_result = mysqli_query($koneksi, $get_query);
    
    if($get_result && mysqli_num_rows($get_result) > 0) {
        $data = mysqli_fetch_assoc($get_result);
        
        $query = "DELETE FROM transaksi WHERE id = '$id' AND jenis = 'pengeluaran'";
        
        if(mysqli_query($koneksi, $query)) {
            // Log aktivitas
            $activity = "Menghapus pengeluaran untuk " . $data['sumber'] . " sebesar Rp " . number_format($data['jumlah'], 0, ',', '.');
            $log_query = "INSERT INTO activity_log (user_id, activity) VALUES ('$user_id', '$activity')";
            mysqli_query($koneksi, $log_query);
            
            $success = "Pengeluaran berhasil dihapus!";
        } else {
            $error = "Gagal menghapus pengeluaran: " . mysqli_error($koneksi);
        }
    } else {
        $error = "Data pengeluaran tidak ditemukan!";
    }
}

// Get filter parameters
$kategori_filter = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';
$bulan_filter = isset($_GET['bulan']) ? mysqli_real_escape_string($koneksi, $_GET['bulan']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Build where clause
$where_clause = "WHERE jenis = 'pengeluaran'";
if($kategori_filter) {
    $where_clause .= " AND kategori = '$kategori_filter'";
}
if($bulan_filter) {
    $where_clause .= " AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_filter'";
}
if($search) {
    $where_clause .= " AND (sumber LIKE '%$search%' OR keterangan LIKE '%$search%')";
}

// Get pengeluaran data
$query = "SELECT t.*, u.nama as user_nama 
          FROM transaksi t
          LEFT JOIN users u ON t.user_id = u.id
          $where_clause
          ORDER BY t.tanggal DESC";
$result = mysqli_query($koneksi, $query);

// Calculate statistics
$stats_query = "SELECT 
                COUNT(*) as total_transaksi,
                COALESCE(SUM(jumlah), 0) as total_pengeluaran,
                COALESCE(AVG(jumlah), 0) as avg_pengeluaran
                FROM transaksi
                WHERE jenis = 'pengeluaran'";
$stats_result = mysqli_query($koneksi, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get current month expense
$current_month = date('Y-m');
$monthly_query = "SELECT COALESCE(SUM(jumlah), 0) as monthly_expense 
                  FROM transaksi 
                  WHERE jenis = 'pengeluaran' 
                  AND DATE_FORMAT(tanggal, '%Y-%m') = '$current_month'";
$monthly_result = mysqli_query($koneksi, $monthly_query);
$monthly_data = mysqli_fetch_assoc($monthly_result);

// Get categories for dropdown (dari transaksi yang sudah ada)
$kategori_query = "SELECT DISTINCT kategori FROM transaksi WHERE kategori IS NOT NULL AND jenis = 'pengeluaran' ORDER BY kategori";
$kategori_result = mysqli_query($koneksi, $kategori_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - SiKaslinggar</title>
    <link rel="shortcut icon" href="assets/img/logokabupaten.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: #059669;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-subtitle {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 300;
        }

        .nav-menu {
            list-style: none;
            padding: 15px 0;
        }

        .nav-item {
            margin-bottom: 2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            cursor: pointer;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #10b981;
            padding-left: 24px;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            border-left: 4px solid #10b981;
        }

        .nav-link-content {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .nav-link i {
            margin-right: 12px;
            width: 18px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-link-text {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .submenu-arrow {
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }

        .submenu-arrow.open {
            transform: rotate(90deg);
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.1);
            transition: max-height 0.3s ease;
        }

        .submenu.open {
            max-height: 300px;
        }

        .submenu-item {
            padding-left: 20px;
        }

        .submenu-link {
            display: block;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            cursor: pointer;
            border-left: 4px solid transparent;
        }

        .submenu-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding-left: 24px;
            border-left: 4px solid #10b981;
        }

        .submenu-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left: 4px solid #10b981;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
            overflow-x: hidden;
            transition: margin-left 0.3s ease;
        }

        .header {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(5, 150, 105, 0.3);
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .header h1 i {
            margin-right: 15px;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 50%;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .stat-content {
            text-align: left;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #374151;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        /* Pengeluaran Management Section */
        .pengeluaran-management {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .pengeluaran-header {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pengeluaran-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .pengeluaran-body {
            padding: 20px;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #059669;
            color: white;
        }

        .btn-primary:hover {
            background: #047857;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: #ffffff;
            border: 2px solid #059669;
        }

        .btn-outline:hover {
            background: #059669;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-info {
            background: #3b82f6;
            color: white;
        }

        .btn-info:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        /* Search and Filter */
        .search-filter {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-box {
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .search-box input:focus {
            outline: none;
            border-color: #059669;
        }

        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-dropdown {
            padding: 10px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
        }

        .filter-dropdown:focus {
            outline: none;
            border-color: #059669;
        }

        .date-input {
            padding: 10px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            background: white;
        }

        .date-input:focus {
            outline: none;
            border-color: #059669;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }

        .pengeluaran-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .pengeluaran-table th,
        .pengeluaran-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .pengeluaran-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }

        .pengeluaran-table tr:hover {
            background: #f9fafb;
        }

        .kategori-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            background: #3b82f6;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal-overlay.show .modal {
            transform: scale(1);
        }

        .modal-header {
            padding: 20px;
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: none;
        }

        .modal-header h3 {
            margin: 0;
            color: white;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.8);
            padding: 5px;
        }

        .modal-close:hover {
            color: white;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #374151;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #059669;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        /* Detail Modal Specific Styles */
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .detail-value {
            font-size: 1rem;
            color: #374151;
            font-weight: 600;
        }

        .detail-id {
            font-family: 'Courier New', monospace;
            background: #fef3c7;
            padding: 8px 12px;
            border-radius: 6px;
            color: #d97706;
            border: 1px solid #fbbf24;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #059669;
            border: 1px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #ef4444;
        }

        /* Mobile Toggle Button */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            background: #059669;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
            font-size: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }

            .sidebar {
                width: 280px;
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 70px 15px 20px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .search-filter {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-controls {
                flex-wrap: wrap;
            }

            .search-box {
                max-width: none;
            }

            .action-buttons {
                flex-direction: column;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>SIKASLINGGAR</h2>
                <p class="sidebar-subtitle">Sistem Informasi Kas Linggar</p>
            </div>

            <ul class="nav-menu">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <div class="nav-link-content">
                            <i class="fas fa-home"></i>
                            <span class="nav-link-text">Dashboard</span>
                        </div>
                    </a>
                </li>

                <!-- Keuangan (Active) -->
                <li class="nav-item">
                    <a href="#" class="nav-link active" onclick="toggleSubmenu(this)">
                        <div class="nav-link-content">
                            <i class="fas fa-wallet"></i>
                            <span class="nav-link-text">Keuangan</span>
                        </div>
                        <i class="fas fa-chevron-right submenu-arrow open"></i>
                    </a>
                    <ul class="submenu open">
                        <li class="submenu-item">
                            <a href="pemasukan.php" class="submenu-link">Pemasukan</a>
                        </li>
                        <li class="submenu-item">
                            <a href="pengeluaran.php" class="submenu-link active">Pengeluaran</a>
                        </li>
                        <li class="submenu-item">
                            <a href="laporan.php" class="submenu-link">Laporan Keuangan</a>
                        </li>
                    </ul>
                </li>
                
                <!-- Pengguna (Admin only) -->
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a href="pengguna.php" class="nav-link">
                        <div class="nav-link-content">
                            <i class="fas fa-users"></i>
                            <span class="nav-link-text">Pengguna</span>
                        </div>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Logout -->
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <div class="nav-link-content">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="nav-link-text">Logout</span>
                        </div>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Alert Messages -->
            <?php if(isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-arrow-down"></i> Kelola Pengeluaran</h1>
                <p>Sistem manajemen pengeluaran SikasLinggar - Kelola pengeluaran dengan mudah</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number" id="totalPengeluaran">
                                Rp <?php echo number_format($stats['total_pengeluaran'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Total Pengeluaran</div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number" id="totalTransaksi">
                                <?php echo $stats['total_transaksi']; ?>
                            </div>
                            <div class="stat-label">Total Transaksi</div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number" id="pengeluaranBulanIni">
                                Rp <?php echo number_format($monthly_data['monthly_expense'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Pengeluaran Bulan Ini</div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number" id="avgPerTransaksi">
                                Rp <?php echo number_format($stats['avg_pengeluaran'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Rata-rata per Transaksi</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengeluaran Management Section -->
            <div class="pengeluaran-management">
                <div class="pengeluaran-header">
                    <h2 class="pengeluaran-title">
                        <i class="fas fa-minus-circle" style="margin-right: 10px;"></i>
                        Manajemen Pengeluaran
                    </h2>
                    <button class="btn btn-outline" onclick="openAddModal()">
                        <i class="fas fa-plus"></i>
                        Tambah Pengeluaran
                    </button>
                </div>

                <div class="pengeluaran-body">
                    <!-- Search and Filter -->
                    <form method="GET" class="search-filter">
                        <div class="search-box">
                            <input type="text" name="search" id="searchInput" placeholder="Cari berdasarkan tujuan, kategori, atau keterangan..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="filter-controls">
                            <select name="kategori" id="kategoriFilter" class="filter-dropdown">
                                <option value="">Semua Kategori</option>
                                <option value="Operasional" <?php echo $kategori_filter == 'Operasional' ? 'selected' : ''; ?>>Operasional</option>
                                <option value="Kegiatan" <?php echo $kategori_filter == 'Kegiatan' ? 'selected' : ''; ?>>Kegiatan</option>
                                <option value="Pemeliharaan" <?php echo $kategori_filter == 'Pemeliharaan' ? 'selected' : ''; ?>>Pemeliharaan</option>
                                <option value="Sosial" <?php echo $kategori_filter == 'Sosial' ? 'selected' : ''; ?>>Sosial</option>
                                <option value="Administrasi" <?php echo $kategori_filter == 'Administrasi' ? 'selected' : ''; ?>>Administrasi</option>
                                <option value="Lainnya" <?php echo $kategori_filter == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                            </select>
                            <input type="month" name="bulan" id="bulanFilter" class="date-input" value="<?php echo $bulan_filter; ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </form>

                    <!-- Pengeluaran Table -->
                    <div class="table-container">
                        <table class="pengeluaran-table" id="pengeluaranTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Tujuan</th>
                                    <th>Kategori</th>
                                    <th>Jumlah</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="pengeluaranTableBody">
                                <?php 
                                if($result && mysqli_num_rows($result) > 0):
                                    $no = 1;
                                    while($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['sumber']); ?></td>
                                    <td><span class="kategori-badge"><?php echo htmlspecialchars($row['kategori']); ?></span></td>
                                    <td style="font-weight: 600; color: #ef4444;">Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($row['keterangan']) ?: '-'; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-info btn-sm" onclick="showDetail(<?php echo $row['id']; ?>, '<?php echo $row['tanggal']; ?>', '<?php echo htmlspecialchars($row['sumber'], ENT_QUOTES); ?>', '<?php echo $row['kategori']; ?>', <?php echo $row['jumlah']; ?>, '<?php echo htmlspecialchars($row['keterangan'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['user_nama'] ?: 'System', ENT_QUOTES); ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-warning btn-sm" onclick="editPengeluaran(<?php echo $row['id']; ?>, '<?php echo $row['tanggal']; ?>', '<?php echo htmlspecialchars($row['sumber'], ENT_QUOTES); ?>', '<?php echo $row['kategori']; ?>', <?php echo $row['jumlah']; ?>, '<?php echo htmlspecialchars($row['keterangan'], ENT_QUOTES); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus pengeluaran ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 10px; display: block;"></i>
                                        Belum ada data pengeluaran
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Add/Edit Pengeluaran -->
    <div class="modal-overlay" id="pengeluaranModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Pengeluaran Baru</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="pengeluaranForm">
                    <input type="hidden" id="editId" name="id">
                    <input type="hidden" id="formAction" name="tambah_pengeluaran" value="1">
                    
                    <div class="form-group">
                        <label for="tanggal">Tanggal <span style="color: red;">*</span></label>
                        <input type="date" id="tanggal" name="tanggal" required>
                    </div>

                    <div class="form-group">
                        <label for="tujuan">Tujuan Pengeluaran <span style="color: red;">*</span></label>
                        <input type="text" id="tujuan" name="tujuan" placeholder="Contoh: Pembelian ATK, Bayar listrik" required>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori <span style="color: red;">*</span></label>
                        <select id="kategori" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Operasional">Operasional</option>
                            <option value="Kegiatan">Kegiatan</option>
                            <option value="Pemeliharaan">Pemeliharaan</option>
                            <option value="Sosial">Sosial</option>
                            <option value="Administrasi">Administrasi</option>
                            <option value="Lainnya">Lainnya</option>
                            <?php 
                            // Tambahkan kategori lain dari database jika ada
                            if($kategori_result && mysqli_num_rows($kategori_result) > 0):
                                while($kat = mysqli_fetch_assoc($kategori_result)):
                                    // Skip jika sudah ada di option default
                                    if(!in_array($kat['kategori'], ['Operasional', 'Kegiatan', 'Pemeliharaan', 'Sosial', 'Administrasi', 'Lainnya'])):
                            ?>
                            <option value="<?php echo htmlspecialchars($kat['kategori']); ?>"><?php echo htmlspecialchars($kat['kategori']); ?></option>
                            <?php 
                                    endif;
                                endwhile;
                                // Reset pointer untuk penggunaan berikutnya
                                mysqli_data_seek($kategori_result, 0);
                            endif; 
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jumlah">Jumlah (Rp) <span style="color: red;">*</span></label>
                        <input type="number" id="jumlah" name="jumlah" placeholder="Masukkan jumlah dalam rupiah" min="0" step="1000" required>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea id="keterangan" name="keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <span id="submitBtnText">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Pengeluaran -->
    <div class="modal-overlay" id="detailModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="" style="margin-right: 10px;"></i>Detail Pengeluaran</h3>
                <button class="modal-close" onclick="closeDetailModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">ID Transaksi</div>
                        <div class="detail-value detail-id" id="detailIdTransaksi">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tanggal</div>
                        <div class="detail-value" id="detailTanggal">-</div>
                    </div>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Tujuan Pengeluaran</div>
                        <div class="detail-value" id="detailTujuan">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Kategori</div>
                        <div class="detail-value" id="detailKategori">-</div>
                    </div>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Jumlah</div>
                        <div class="detail-value" id="detailJumlah" style="color: #ef4444; font-size: 1.2rem;">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Diinput Oleh</div>
                        <div class="detail-value" id="detailUser">-</div>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Keterangan</div>
                    <div class="detail-value" id="detailKeterangan">-</div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" onclick="closeDetailModal()">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar Functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        function toggleSubmenu(element) {
            const submenu = element.nextElementSibling;
            const arrow = element.querySelector('.submenu-arrow');
            
            submenu.classList.toggle('open');
            arrow.classList.toggle('open');
        }

        // Modal Functions
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Pengeluaran Baru';
            document.getElementById('submitBtnText').textContent = 'Simpan';
            document.getElementById('pengeluaranForm').reset();
            document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];
            document.getElementById('editId').value = '';
            document.getElementById('formAction').name = 'tambah_pengeluaran';
            
            document.getElementById('pengeluaranModal').classList.add('show');
        }

        function editPengeluaran(id, tanggal, tujuan, kategori, jumlah, keterangan) {
            document.getElementById('modalTitle').textContent = 'Edit Pengeluaran';
            document.getElementById('submitBtnText').textContent = 'Update';
            
            document.getElementById('editId').value = id;
            document.getElementById('tanggal').value = tanggal;
            document.getElementById('tujuan').value = tujuan;
            document.getElementById('kategori').value = kategori;
            document.getElementById('jumlah').value = jumlah;
            document.getElementById('keterangan').value = keterangan;
            document.getElementById('formAction').name = 'edit_pengeluaran';
            
            document.getElementById('pengeluaranModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('pengeluaranModal').classList.remove('show');
            document.getElementById('pengeluaranForm').reset();
        }

        // Detail Modal Functions
        function showDetail(id, tanggal, tujuan, kategori, jumlah, keterangan, user) {
            // Generate ID Transaksi
            const tahun = new Date(tanggal).getFullYear();
            const idTransaksi = String(id).padStart(3, '0') + '/SIKAS/' + tahun;
            
            // Format tanggal
            const dateObj = new Date(tanggal);
            const formattedDate = dateObj.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long', 
                year: 'numeric'
            });
            
            // Format jumlah
            const formattedJumlah = 'Rp ' + new Intl.NumberFormat('id-ID').format(jumlah);
            
            // Set values
            document.getElementById('detailIdTransaksi').textContent = idTransaksi;
            document.getElementById('detailTanggal').textContent = formattedDate;
            document.getElementById('detailTujuan').textContent = tujuan;
            document.getElementById('detailKategori').textContent = kategori;
            document.getElementById('detailJumlah').textContent = formattedJumlah;
            document.getElementById('detailKeterangan').textContent = keterangan || '-';
            document.getElementById('detailUser').textContent = user;
            
            document.getElementById('detailModal').classList.add('show');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('show');
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeModal();
                closeDetailModal();
            }
        });

        // Handle escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeDetailModal();
            }
        });
    </script>
</body>
</html>