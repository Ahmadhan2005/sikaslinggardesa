<?php
session_start();

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/koneksi.php';

// Get current month and year
$current_month = date('Y-m');
$current_year = date('Y');

// Get Total Saldo (Pemasukan - Pengeluaran)
$saldo_query = "SELECT 
    COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as total_pemasukan_all,
    COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as total_pengeluaran_all
    FROM transaksi";
$saldo_result = mysqli_query($koneksi, $saldo_query);
$saldo_data = mysqli_fetch_assoc($saldo_result);
$total_saldo = $saldo_data['total_pemasukan_all'] - $saldo_data['total_pengeluaran_all'];

// Get Pemasukan Bulan Ini
$pemasukan_query = "SELECT COALESCE(SUM(jumlah), 0) as pemasukan_bulan_ini 
                    FROM transaksi 
                    WHERE jenis = 'pemasukan' 
                    AND DATE_FORMAT(tanggal, '%Y-%m') = '$current_month'";
$pemasukan_result = mysqli_query($koneksi, $pemasukan_query);
$pemasukan_data = mysqli_fetch_assoc($pemasukan_result);
$pemasukan_bulan_ini = $pemasukan_data['pemasukan_bulan_ini'];

// Get Pengeluaran Bulan Ini
$pengeluaran_query = "SELECT COALESCE(SUM(jumlah), 0) as pengeluaran_bulan_ini 
                      FROM transaksi 
                      WHERE jenis = 'pengeluaran' 
                      AND DATE_FORMAT(tanggal, '%Y-%m') = '$current_month'";
$pengeluaran_result = mysqli_query($koneksi, $pengeluaran_query);
$pengeluaran_data = mysqli_fetch_assoc($pengeluaran_result);
$pengeluaran_bulan_ini = $pengeluaran_data['pengeluaran_bulan_ini'];

// Get Total Pengguna
$pengguna_query = "SELECT COUNT(*) as total_pengguna FROM users";
$pengguna_result = mysqli_query($koneksi, $pengguna_query);
$pengguna_data = mysqli_fetch_assoc($pengguna_result);
$total_pengguna = $pengguna_data['total_pengguna'];

// Calculate percentage changes (comparing with last month)
$last_month = date('Y-m', strtotime('-1 month'));

// Last month saldo
$last_saldo_query = "SELECT 
    COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) -
    COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as saldo_lalu
    FROM transaksi
    WHERE tanggal < DATE_FORMAT(NOW(), '%Y-%m-01')";
$last_saldo_result = mysqli_query($koneksi, $last_saldo_query);
$last_saldo_data = mysqli_fetch_assoc($last_saldo_result);
$saldo_bulan_lalu = $last_saldo_data['saldo_lalu'];

// Last month pemasukan
$last_pemasukan_query = "SELECT COALESCE(SUM(jumlah), 0) as pemasukan_lalu 
                         FROM transaksi 
                         WHERE jenis = 'pemasukan' 
                         AND DATE_FORMAT(tanggal, '%Y-%m') = '$last_month'";
$last_pemasukan_result = mysqli_query($koneksi, $last_pemasukan_query);
$last_pemasukan_data = mysqli_fetch_assoc($last_pemasukan_result);
$pemasukan_bulan_lalu = $last_pemasukan_data['pemasukan_lalu'];

// Last month pengeluaran
$last_pengeluaran_query = "SELECT COALESCE(SUM(jumlah), 0) as pengeluaran_lalu 
                           FROM transaksi 
                           WHERE jenis = 'pengeluaran' 
                           AND DATE_FORMAT(tanggal, '%Y-%m') = '$last_month'";
$last_pengeluaran_result = mysqli_query($koneksi, $last_pengeluaran_query);
$last_pengeluaran_data = mysqli_fetch_assoc($last_pengeluaran_result);
$pengeluaran_bulan_lalu = $last_pengeluaran_data['pengeluaran_lalu'];

// Calculate percentages
$perubahan_saldo = $saldo_bulan_lalu > 0 ? (($total_saldo - $saldo_bulan_lalu) / $saldo_bulan_lalu) * 100 : 0;
$perubahan_pemasukan = $pemasukan_bulan_lalu > 0 ? (($pemasukan_bulan_ini - $pemasukan_bulan_lalu) / $pemasukan_bulan_lalu) * 100 : 0;
$perubahan_pengeluaran = $pengeluaran_bulan_lalu > 0 ? (($pengeluaran_bulan_ini - $pengeluaran_bulan_lalu) / $pengeluaran_bulan_lalu) * 100 : 0;

// Get Recent Transactions (4 latest)
$transaksi_query = "SELECT t.*, u.nama as user_nama 
                    FROM transaksi t
                    LEFT JOIN users u ON t.user_id = u.id
                    ORDER BY t.tanggal DESC, t.id DESC
                    LIMIT 4";
$transaksi_result = mysqli_query($koneksi, $transaksi_query);

// Get Recent Activities from activity_log (4 activities) - Exclude donation activities
$aktivitas_query = "SELECT a.*, u.nama as user_nama 
                    FROM activity_log a
                    LEFT JOIN users u ON a.user_id = u.id
                    ORDER BY a.created_at DESC
                    LIMIT 4";
$aktivitas_result = mysqli_query($koneksi, $aktivitas_query);

// Get data for chart (last 6 months)
$chart_labels = [];
$chart_pemasukan = [];
$chart_pengeluaran = [];

for($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    
    $chart_query = "SELECT 
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as pemasukan,
        COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as pengeluaran
        FROM transaksi 
        WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$month'";
    
    $chart_result = mysqli_query($koneksi, $chart_query);
    $chart_row = mysqli_fetch_assoc($chart_result);
    
    $chart_labels[] = $month_name;
    $chart_pemasukan[] = $chart_row['pemasukan'];
    $chart_pengeluaran[] = $chart_row['pengeluaran'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SiKaslinggar</title>
    <link rel="shortcut icon" href="assets/img/logokabupaten.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

        .submenu-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
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

        .welcome-text {
            font-size: 1.1rem;
            margin-bottom: 8px;
            opacity: 0.95;
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

        .stat-icon.income {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-icon.expense {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .stat-icon.balance {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .stat-icon.users {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .stat-content {
            text-align: left;
            flex: 1;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #374151;
        }

        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .stat-sublabel {
            color: #9ca3af;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .stat-change {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }

        .stat-change.positive {
            color: #10b981;
        }

        .stat-change.negative {
            color: #ef4444;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Transactions Grid */
        .transactions-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 20px;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
        }

        /* Transaction List */
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        .transaction-info {
            flex: 1;
        }

        .transaction-desc {
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
            font-size: 0.95rem;
        }

        .transaction-meta {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .transaction-amount {
            font-weight: 700;
            font-size: 1rem;
        }

        .transaction-amount.pemasukan {
            color: #10b981;
        }

        .transaction-amount.pengeluaran {
            color: #ef4444;
        }

        /* Activity List */
        .activity-item {
            display: flex;
            align-items: flex-start;
            padding: 15px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            background: #f0fdf4;
            color: #059669;
            font-size: 0.9rem;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            color: #374151;
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #9ca3af;
        }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
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
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
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
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .transactions-grid {
                grid-template-columns: 1fr;
            }
        }

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

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .transactions-grid {
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
                    <a href="dashboard.php" class="nav-link active">
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
                
                <!-- Pengguna -->
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
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
            <!-- Header -->
            <header class="header">
                <div>
                    <div class="welcome-text">Selamat Datang!</div>
                    <h1>
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard SIKASLINGGAR
                    </h1>
                    <p>Kelola sistem informasi kas linggar dengan mudah dan efisien</p>
                </div>
            </header>

            <!-- Statistics Grid -->
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number">Rp <?php echo number_format($total_saldo, 0, ',', '.'); ?></div>
                            <div class="stat-label">Total Saldo</div>
                            <div class="stat-change <?php echo $perubahan_saldo >= 0 ? 'positive' : 'negative'; ?>">
                                <i class="fas fa-arrow-<?php echo $perubahan_saldo >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo abs(round($perubahan_saldo, 1)); ?>% dari bulan lalu
                            </div>
                        </div>
                        <div class="stat-icon balance">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number">Rp <?php echo number_format($pemasukan_bulan_ini, 0, ',', '.'); ?></div>
                            <div class="stat-label">Pemasukan Bulan Ini</div>
                            <div class="stat-change <?php echo $perubahan_pemasukan >= 0 ? 'positive' : 'negative'; ?>">
                                <i class="fas fa-arrow-<?php echo $perubahan_pemasukan >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo abs(round($perubahan_pemasukan, 1)); ?>% dari bulan lalu
                            </div>
                        </div>
                        <div class="stat-icon income">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number">Rp <?php echo number_format($pengeluaran_bulan_ini, 0, ',', '.'); ?></div>
                            <div class="stat-label">Pengeluaran Bulan Ini</div>
                            <div class="stat-change <?php echo $perubahan_pengeluaran <= 0 ? 'positive' : 'negative'; ?>">
                                <i class="fas fa-arrow-<?php echo $perubahan_pengeluaran >= 0 ? 'up' : 'down'; ?>"></i>
                                <?php echo abs(round($perubahan_pengeluaran, 1)); ?>% dari bulan lalu
                            </div>
                        </div>
                        <div class="stat-icon expense">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $total_pengguna; ?></div>
                            <div class="stat-label">Total Pengguna</div>
                            <div class="stat-sublabel">Terdaftar dalam sistem</div>
                        </div>
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line"></i>
                            Tren Keuangan Bulanan
                        </h3>
                        <a href="laporan.php" class="btn btn-outline">
                            <i class="fas fa-external-link-alt"></i>
                            Detail
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="keuanganChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Activities -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock"></i>
                            Aktivitas Terbaru
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        if($aktivitas_result && mysqli_num_rows($aktivitas_result) > 0):
                            while($aktivitas = mysqli_fetch_assoc($aktivitas_result)):
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <strong><?php echo htmlspecialchars($aktivitas['user_nama'] ?: 'System'); ?></strong>
                                    <?php echo htmlspecialchars($aktivitas['activity']); ?>
                                </div>
                                <div class="activity-time">
                                    <?php echo date('d M Y, H:i', strtotime($aktivitas['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <div style="text-align: center; padding: 20px; color: #6b7280;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            Belum ada aktivitas
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Transactions Grid -->
            <div class="transactions-grid">
                <!-- Recent Transactions Keuangan -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exchange-alt"></i>
                            Transaksi Keuangan Terbaru
                        </h3>
                        <a href="pemasukan.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Tambah
                        </a>
                    </div>
                    <div class="card-body">
                        <?php 
                        if($transaksi_result && mysqli_num_rows($transaksi_result) > 0):
                            while($transaksi = mysqli_fetch_assoc($transaksi_result)):
                        ?>
                        <div class="transaction-item">
                            <div class="transaction-info">
                                <div class="transaction-desc">
                                    <?php echo htmlspecialchars($transaksi['keterangan'] ?: $transaksi['sumber']); ?>
                                </div>
                                <div class="transaction-meta">
                                    <?php echo date('d M Y', strtotime($transaksi['tanggal'])); ?> • 
                                    <span style="color: #059669;"><?php echo htmlspecialchars($transaksi['kategori']); ?></span>
                                </div>
                            </div>
                            <div class="transaction-amount <?php echo $transaksi['jenis']; ?>">
                                <?php echo $transaksi['jenis'] == 'pemasukan' ? '+' : '-'; ?>
                                Rp <?php echo number_format($transaksi['jumlah'], 0, ',', '.'); ?>
                            </div>
                        </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <div style="text-align: center; padding: 40px; color: #6b7280;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; display: block; color: #cbd5e0;"></i>
                            Belum ada transaksi
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        function toggleSubmenu(element) {
            const submenu = element.nextElementSibling;
            const arrow = element.querySelector('.submenu-arrow');
            
            // Close other submenus
            document.querySelectorAll('.submenu.open').forEach(menu => {
                if (menu !== submenu) {
                    menu.classList.remove('open');
                    menu.previousElementSibling.querySelector('.submenu-arrow').classList.remove('open');
                }
            });

            submenu.classList.toggle('open');
            arrow.classList.toggle('open');
        }

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Finance Chart - Bar Chart seperti di laporan.php
            const financeCtx = document.getElementById('keuanganChart').getContext('2d');
            new Chart(financeCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chart_labels); ?>,
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: <?php echo json_encode($chart_pemasukan); ?>,
                            backgroundColor: 'rgba(5, 150, 105, 0.8)',
                            borderColor: 'rgba(5, 150, 105, 1)',
                            borderWidth: 2,
                            borderRadius: 5
                        },
                        {
                            label: 'Pengeluaran',
                            data: <?php echo json_encode($chart_pengeluaran); ?>,
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 2,
                            borderRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                                    } else if (value >= 1000) {
                                        return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                    }
                                    return 'Rp ' + value;
                                }
                            },
                            grid: {
                                borderDash: [5, 5]
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });

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

        // Auto refresh data every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>