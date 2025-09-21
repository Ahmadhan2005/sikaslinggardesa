<?php
// File: pengguna.php
session_start();
require_once 'config/koneksi.php';

// Cek login dan role admin
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Handle actions (update role, status, delete)
if(isset($_POST['action'])) {
    $user_id = mysqli_real_escape_string($koneksi, $_POST['user_id']);
    $action = $_POST['action'];
    
    switch($action) {
        case 'update_role':
            $current_role = mysqli_real_escape_string($koneksi, $_POST['current_role']);
            $new_role = ($current_role == 'admin') ? 'user' : 'admin';
            $query = "UPDATE users SET role = '$new_role' WHERE id = '$user_id'";
            if(mysqli_query($koneksi, $query)) {
                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Role berhasil diubah!'];
            } else {
                $_SESSION['notification'] = ['type' => 'error', 'message' => 'Gagal mengubah role!'];
            }
            break;
            
        case 'toggle_status':
            $current_status = mysqli_real_escape_string($koneksi, $_POST['current_status']);
            $new_status = ($current_status == 'aktif') ? 'nonaktif' : 'aktif';
            $query = "UPDATE users SET status = '$new_status' WHERE id = '$user_id'";
            if(mysqli_query($koneksi, $query)) {
                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Status berhasil diubah!'];
            } else {
                $_SESSION['notification'] = ['type' => 'error', 'message' => 'Gagal mengubah status!'];
            }
            break;
            
        case 'delete':
            // Don't allow deleting own account
            if($user_id == $_SESSION['user_id']) {
                $_SESSION['notification'] = ['type' => 'error', 'message' => 'Tidak dapat menghapus akun sendiri!'];
            } else {
                $query = "DELETE FROM users WHERE id = '$user_id'";
                if(mysqli_query($koneksi, $query)) {
                    $_SESSION['notification'] = ['type' => 'success', 'message' => 'Pengguna berhasil dihapus!'];
                } else {
                    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Gagal menghapus pengguna!'];
                }
            }
            break;
            
        case 'reset_password':
            $new_password = password_hash('password123', PASSWORD_DEFAULT);
            $query = "UPDATE users SET password = '$new_password' WHERE id = '$user_id'";
            if(mysqli_query($koneksi, $query)) {
                $_SESSION['notification'] = ['type' => 'success', 'message' => 'Password berhasil direset ke: password123'];
            } else {
                $_SESSION['notification'] = ['type' => 'error', 'message' => 'Gagal reset password!'];
            }
            break;
    }
    
    header("Location: pengguna.php");
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_role = isset($_GET['role']) ? mysqli_real_escape_string($koneksi, $_GET['role']) : '';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : '';

// Build query with filters
$where_clause = "WHERE 1=1";
if($search) {
    $where_clause .= " AND (nama LIKE '%$search%' OR email LIKE '%$search%')";
}
if($filter_role) {
    $where_clause .= " AND role = '$filter_role'";
}
if($filter_status) {
    $where_clause .= " AND status = '$filter_status'";
}

// Add status column if not exists
$check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'status'");
if(mysqli_num_rows($check_column) == 0) {
    mysqli_query($koneksi, "ALTER TABLE users ADD COLUMN status ENUM('aktif', 'nonaktif') DEFAULT 'aktif' AFTER role");
}

// Add created_at column if not exists
$check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'created_at'");
if(mysqli_num_rows($check_column) == 0) {
    mysqli_query($koneksi, "ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

// Get users data
$query = "SELECT * FROM users $where_clause ORDER BY created_at DESC";
$result = mysqli_query($koneksi, $query);

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_count,
                COUNT(CASE WHEN role = 'user' THEN 1 END) as user_count,
                COUNT(CASE WHEN status = 'aktif' THEN 1 END) as active_count,
                COUNT(CASE WHEN status = 'nonaktif' THEN 1 END) as inactive_count
                FROM users";
$stats_result = mysqli_query($koneksi, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - SiKaslinggar</title>
    <link rel="shortcut icon" href="assets/img/logokabupaten.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* === RESET & BASE STYLES === */
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

        /* === NOTIFICATION STYLES === */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
            }
            to {
                transform: translateX(0);
            }
        }

        .notification.success {
            background: #10b981;
            color: white;
        }

        .notification.error {
            background: #ef4444;
            color: white;
        }

        .notification-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
        }

        /* === SIDEBAR STYLES === */
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
            max-height: 500px;
        }

        .submenu-item {
            padding-left: 50px;
        }

        .submenu-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .submenu-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding-left: 24px;
        }

        /* === MAIN CONTENT STYLES === */
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

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        .stat-icon.total {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .stat-icon.admin {
            background: linear-gradient(135deg, #059669, #047857);
        }

        .stat-icon.user {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .stat-icon.active {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-icon.inactive {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .stat-content {
            text-align: left;
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #374151;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            min-width: 300px;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .search-input:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .filter-dropdown {
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
            background: white;
            cursor: pointer;
        }

        .filter-dropdown:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .table-header {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
            color: #6b7280;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        /* Badge Styles */
        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            gap: 5px;
        }

        .role-badge.admin {
            background: #d1fae5;
            color: #059669;
        }

        .role-badge.user {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            gap: 5px;
        }

        .status-badge.aktif {
            background: #d1fae5;
            color: #059669;
        }

        .status-badge.nonaktif {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Action Buttons */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .btn-primary {
            background: #059669;
            color: white;
        }

        .btn-primary:hover {
            background: #047857;
            transform: translateY(-2px);
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-info {
            background: #3b82f6;
            color: white;
        }

        .btn-info:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-group {
            display: flex;
            gap: 8px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            line-height: 1;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
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

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filter-controls {
                flex-direction: column;
                width: 100%;
            }

            .search-box {
                width: 100%;
                min-width: unset;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 10px 5px;
            }

            .btn {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    <!-- Notification -->
    <?php if(isset($_SESSION['notification'])): ?>
    <div class="notification <?php echo $_SESSION['notification']['type']; ?>" id="notification">
        <i class="fas fa-<?php echo $_SESSION['notification']['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <span><?php echo $_SESSION['notification']['message']; ?></span>
        <button class="notification-close" onclick="hideNotification()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>

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

                 <!-- Keuangan -->
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="toggleSubmenu(this)">
                        <div class="nav-link-content">
                            <i class="fas fa-wallet"></i>
                            <span class="nav-link-text">Keuangan</span>
                        </div>
                        <i class="fas fa-chevron-right submenu-arrow"></i>
                    </a>
                    <ul class="submenu">
                        <li class="submenu-item">
                            <a href="pemasukan.php" class="submenu-link">Pemasukan</a>
                        </li>
                        <li class="submenu-item">
                            <a href="pengeluaran.php" class="submenu-link">Pengeluaran</a>
                        </li>
                        <li class="submenu-item">
                            <a href="laporan.php" class="submenu-link">Laporan Keuangan</a>
                        </li>
                    </ul>
                </li>

                <!-- Pengguna (Active) -->
                <li class="nav-item">
                    <a href="pengguna.php" class="nav-link active">
                        <div class="nav-link-content">
                            <i class="fas fa-users"></i>
                            <span class="nav-link-text">Pengguna</span>
                        </div>
                    </a>
                </li>

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
            <!-- Header -->
            <header class="header">
                <div>
                    <h1>
                        <i class="fas fa-users"></i>
                        Manajemen Pengguna
                    </h1>
                    <p>Kelola data pengguna sistem SIKASLINGGAR</p>
                </div>
            </header>

            <!-- Statistics Grid -->
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                            <div class="stat-label">Total Pengguna</div>
                        </div>
                        <div class="stat-icon total">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['admin_count'] ?? 0; ?></div>
                            <div class="stat-label">Admin</div>
                        </div>
                        <div class="stat-icon admin">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['user_count'] ?? 0; ?></div>
                            <div class="stat-label">User</div>
                        </div>  
                        <div class="stat-icon user">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['active_count'] ?? 0; ?></div>
                            <div class="stat-label">User Aktif</div>
                        </div>
                        <div class="stat-icon active">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['inactive_count'] ?? 0; ?></div>
                            <div class="stat-label">User Nonaktif</div>
                        </div>
                        <div class="stat-icon inactive">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filter Section -->
            <section class="filter-section">
                <form method="GET" class="filter-controls">
                    <div class="search-box">
                        <input type="text" name="search" class="search-input" placeholder="Cari nama atau email..." value="<?php echo $search; ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Cari
                        </button>
                    </div>
                    <select name="role" class="filter-dropdown">
                        <option value="">Semua Role</option>
                        <option value="admin" <?php echo $filter_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="user" <?php echo $filter_role == 'user' ? 'selected' : ''; ?>>User</option>
                    </select>
                    <select name="status" class="filter-dropdown">
                        <option value="">Semua Status</option>
                        <option value="aktif" <?php echo $filter_status == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="nonaktif" <?php echo $filter_status == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i>
                        Filter
                    </button>
                    <a href="pengguna.php" class="btn btn-warning">
                        <i class="fas fa-sync"></i>
                        Reset
                    </a>
                </form>
            </section>

            <!-- Users Table -->
            <section class="table-container">
                <div class="table-header">
                    <div class="table-title">Daftar Pengguna</div>
                </div>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Tgl Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($result) > 0):
                                $no = 1;
                                while($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="role-badge <?php echo $row['role']; ?>">
                                        <i class="fas fa-<?php echo $row['role'] == 'admin' ? 'user-shield' : 'user'; ?>"></i>
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $row['status']; ?>">
                                        <i class="fas fa-<?php echo $row['status'] == 'aktif' ? 'check-circle' : 'times-circle'; ?>"></i>
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $row['status']; ?>">
                                            <input type="hidden" name="current_role" value="<?php echo $row['role']; ?>">
                                            
                                            <?php if($row['id'] != $_SESSION['user_id']): ?>
                                                <!-- Role Toggle Button -->
                                                <button type="submit" name="action" value="update_role" class="btn <?php echo $row['role'] == 'admin' ? 'btn-warning' : 'btn-success'; ?>" onclick="return confirm('Yakin ingin mengubah role pengguna ini menjadi <?php echo $row['role'] == 'admin' ? 'User' : 'Admin'; ?>?')">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                                
                                                <!-- Existing buttons -->
                                                <button type="submit" name="action" value="toggle_status" class="btn <?php echo $row['status'] == 'aktif' ? 'btn-warning' : 'btn-success'; ?>" onclick="return confirm('Yakin ingin mengubah status pengguna ini?')">
                                                    <i class="fas fa-<?php echo $row['status'] == 'aktif' ? 'ban' : 'check'; ?>"></i>
                                                </button>
                                                
                                                <button type="submit" name="action" value="reset_password" class="btn btn-info" onclick="return confirm('Reset password pengguna ini ke default (password123)?')">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                                
                                                <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px;">
                                    <i class="fas fa-users" style="font-size: 3rem; color: #d1d5db; margin-bottom: 10px;"></i>
                                    <p>Tidak ada data pengguna</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Auto hide notification after 5 seconds
        const notification = document.getElementById('notification');
        if(notification) {
            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000);
        }

        function hideNotification() {
            const notification = document.getElementById('notification');
            notification.style.display = 'none';
        }

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

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
</body>
</html>