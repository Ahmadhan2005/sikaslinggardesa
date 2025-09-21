<?php
session_start();

// Cek login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/koneksi.php';

// Function untuk generate ID Transaksi - DIPERBAIKI
function generateTransaksiId($id, $tanggal) {
    $tahun = date('Y', strtotime($tanggal));
    return sprintf("%03d/SIKAS/%s", $id, $tahun);
}

// Get filter parameters dengan default values
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$jenis_filter = isset($_GET['jenis']) ? mysqli_real_escape_string($koneksi, $_GET['jenis']) : 'all';
$kategori_filter = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';

// Build where clause
$where_clause = "WHERE tanggal BETWEEN '$date_from' AND '$date_to'";
if($jenis_filter != 'all') {
    $where_clause .= " AND jenis = '$jenis_filter'";
}
if($kategori_filter) {
    $where_clause .= " AND kategori = '$kategori_filter'";
}
if($search) {
    $where_clause .= " AND (keterangan LIKE '%$search%' OR sumber LIKE '%$search%' OR kategori LIKE '%$search%')";
}

// Initialize statistics with default values
$stats = [
    'total_transaksi' => 0,
    'total_pemasukan' => 0,
    'total_pengeluaran' => 0,
    'saldo' => 0,
    'avg_pemasukan' => 0,
    'avg_pengeluaran' => 0,
    'max_transaksi' => 0,
    'unique_categories' => 0
];

// Get statistics from transaksi table
$stats_query = "SELECT 
    COUNT(*) as total_transaksi,
    COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as total_pemasukan,
    COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as total_pengeluaran,
    COALESCE(AVG(CASE WHEN jenis = 'pemasukan' THEN jumlah END), 0) as avg_pemasukan,
    COALESCE(AVG(CASE WHEN jenis = 'pengeluaran' THEN jumlah END), 0) as avg_pengeluaran,
    COALESCE(MAX(jumlah), 0) as max_transaksi,
    COUNT(DISTINCT kategori) as unique_categories
    FROM transaksi 
    $where_clause";

$stats_result = mysqli_query($koneksi, $stats_query);
if($stats_result && mysqli_num_rows($stats_result) > 0) {
    $stats = mysqli_fetch_assoc($stats_result);
}

// Calculate saldo
$stats['saldo'] = $stats['total_pemasukan'] - $stats['total_pengeluaran'];

// Get category breakdown
$category_data = [];
$category_query = "SELECT 
                   kategori,
                   COUNT(*) as count,
                   COALESCE(SUM(jumlah), 0) as total,
                   jenis
                   FROM transaksi t
                   $where_clause
                   GROUP BY kategori, jenis
                   ORDER BY total DESC";
$category_result = mysqli_query($koneksi, $category_query);
if($category_result) {
    while($row = mysqli_fetch_assoc($category_result)) {
        $category_data[] = $row;
    }
}

// Get monthly data for chart (last 6 months)
$chart_labels = [];
$chart_pemasukan = [];
$chart_pengeluaran = [];

for($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    
    $monthly_query = "SELECT 
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE 0 END), 0) as pemasukan,
        COALESCE(SUM(CASE WHEN jenis = 'pengeluaran' THEN jumlah ELSE 0 END), 0) as pengeluaran
        FROM transaksi 
        WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$month'";
    
    $monthly_result = mysqli_query($koneksi, $monthly_query);
    $monthly_data = mysqli_fetch_assoc($monthly_result);
    
    $chart_labels[] = $month_name;
    $chart_pemasukan[] = $monthly_data['pemasukan'];
    $chart_pengeluaran[] = $monthly_data['pengeluaran'];
}

// Get all transactions for detail table
$detail_query = "SELECT t.*, u.nama as user_nama 
                FROM transaksi t
                LEFT JOIN users u ON t.user_id = u.id
                $where_clause
                ORDER BY t.tanggal DESC, t.id DESC";
$detail_result = mysqli_query($koneksi, $detail_query);

// Export to Excel functionality - DIPERBAIKI
if(isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Set headers for Excel download
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=laporan_keuangan_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // Output Excel content - DIPERBAIKI: Hapus kolom "Diinput Oleh"
    echo "<table border='1'>";
    echo "<tr><th colspan='7'>LAPORAN KEUANGAN SIKASLINGGAR</th></tr>";
    echo "<tr><th colspan='7'>Periode: " . date('d/m/Y', strtotime($date_from)) . " - " . date('d/m/Y', strtotime($date_to)) . "</th></tr>";
    echo "<tr><th>No</th><th>ID Transaksi</th><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th>Jumlah</th></tr>";
    
    $no = 1;
    if($detail_result) {
        mysqli_data_seek($detail_result, 0);
        while($row = mysqli_fetch_assoc($detail_result)) {
            $id_transaksi = generateTransaksiId($row['id'], $row['tanggal']);
            echo "<tr>";
            echo "<td>$no</td>";
            echo "<td>$id_transaksi</td>";
            echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
            echo "<td>" . ucfirst($row['jenis']) . "</td>";
            echo "<td>" . htmlspecialchars($row['kategori']) . "</td>";
            echo "<td>" . htmlspecialchars($row['keterangan'] ?: $row['sumber']) . "</td>";
            echo "<td>" . number_format($row['jumlah'], 0, ',', '.') . "</td>";
            echo "</tr>";
            $no++;
        }
    }
    
    // DIPERBAIKI: Total dipindah ke baris bawah dengan format hitam putih
    echo "<tr style='font-weight: bold;'>";
    echo "<td colspan='6'>Total Pemasukan</td>";
    echo "<td>" . number_format($stats['total_pemasukan'], 0, ',', '.') . "</td>";
    echo "</tr>";
    
    echo "<tr style='font-weight: bold;'>";
    echo "<td colspan='6'>Total Pengeluaran</td>";
    echo "<td>" . number_format($stats['total_pengeluaran'], 0, ',', '.') . "</td>";
    echo "</tr>";
    
    echo "<tr style='font-weight: bold;'>";
    echo "<td colspan='6'>Saldo</td>";
    echo "<td>" . number_format($stats['saldo'], 0, ',', '.') . "</td>";
    echo "</tr>";
    
    echo "</table>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - SiKaslinggar</title>
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

        .stat-icon.transaction {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
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

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #374151;
        }

        .filter-controls {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .filter-input,
        .filter-dropdown {
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.95rem;
            background: white;
        }

        .filter-input:focus,
        .filter-dropdown:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            font-size: 1.2rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        /* Breakdown Cards */
        .breakdown-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .breakdown-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .breakdown-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 15px;
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .breakdown-item:last-child {
            border-bottom: none;
        }

        .breakdown-label {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .breakdown-indicator {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        .breakdown-value {
            font-weight: 600;
            color: #374151;
        }

        .breakdown-percentage {
            font-size: 0.85rem;
            color: #6b7280;
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

        .table-actions {
            display: flex;
            gap: 10px;
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
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            gap: 5px;
        }

        .status-badge.pemasukan {
            background: #d1fae5;
            color: #059669;
        }

        .status-badge.pengeluaran {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Category badges */
        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .category-badge.iuran { background: #dbeafe; color: #2563eb; }
        .category-badge.bantuan { background: #ecfccb; color: #65a30d; }
        .category-badge.operasional { background: #fee2e2; color: #dc2626; }
        .category-badge.kegiatan { background: #e0e7ff; color: #6366f1; }
        .category-badge.pemeliharaan { background: #fdf2f8; color: #ec4899; }

        /* Transaksi ID styling */
        .transaksi-id {
            font-family: 'Courier New', monospace;
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            border: 1px solid #e2e8f0;
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

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
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

        /* Amount styling */
        .amount-in {
            color: #059669;
            font-weight: 600;
        }

        .amount-out {
            color: #ef4444;
            font-weight: 600;
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
            .charts-grid {
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

            .filter-controls {
                grid-template-columns: 1fr;
            }

            .breakdown-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 10px 5px;
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
                            <a href="pengeluaran.php" class="submenu-link">Pengeluaran</a>
                        </li>
                        <li class="submenu-item">
                            <a href="laporan.php" class="submenu-link active">Laporan Keuangan</a>
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
                    <h1>
                        <i class="fas fa-chart-line"></i>
                        Laporan Keuangan
                    </h1>
                    <p>Analisis dan laporan lengkap keuangan SIKASLINGGAR</p>
                </div>
            </header>

            <!-- Statistics Grid -->
            <section class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number">Rp <?php echo number_format($stats['total_pemasukan'], 0, ',', '.'); ?></div>
                            <div class="stat-label">Total Pemasukan</div>
                            <div class="stat-sublabel">Periode: <?php echo date('d M', strtotime($date_from)); ?> - <?php echo date('d M Y', strtotime($date_to)); ?></div>
                        </div>
                        <div class="stat-icon income">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number">Rp <?php echo number_format($stats['total_pengeluaran'], 0, ',', '.'); ?></div>
                            <div class="stat-label">Total Pengeluaran</div>
                            <div class="stat-sublabel"><?php echo $stats['total_transaksi'] > 0 ? round(($stats['total_pengeluaran']/($stats['total_pemasukan']+$stats['total_pengeluaran']))*100, 1) : 0; ?>% dari total</div>
                        </div>
                        <div class="stat-icon expense">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number">Rp <?php echo number_format($stats['saldo'], 0, ',', '.'); ?></div>
                            <div class="stat-label">Saldo Akhir</div>
                            <div class="stat-sublabel"><?php echo $stats['saldo'] >= 0 ? 'Surplus' : 'Defisit'; ?></div>
                        </div>
                        <div class="stat-icon balance">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['total_transaksi']; ?></div>
                            <div class="stat-label">Total Transaksi</div>
                            <div class="stat-sublabel">Periode: <?php echo date('d M', strtotime($date_from)); ?> - <?php echo date('d M Y', strtotime($date_to)); ?></div>
                        </div>
                        <div class="stat-icon transaction">
                            <i class="fas fa-list-alt"></i>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filter Section -->
            <section class="filter-section">
                <form method="GET" class="filter-controls">
                    <div class="filter-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="date_from" class="filter-input" value="<?php echo $date_from; ?>">
                    </div>
                    <div class="filter-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="date_to" class="filter-input" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="filter-group">
                        <label>Jenis Transaksi</label>
                        <select name="jenis" class="filter-dropdown">
                            <option value="all" <?php echo $jenis_filter == 'all' ? 'selected' : ''; ?>>Semua Jenis</option>
                            <option value="pemasukan" <?php echo $jenis_filter == 'pemasukan' ? 'selected' : ''; ?>>Pemasukan</option>
                            <option value="pengeluaran" <?php echo $jenis_filter == 'pengeluaran' ? 'selected' : ''; ?>>Pengeluaran</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="laporan.php" class="btn btn-info">
                            <i class="fas fa-sync"></i> Reset
                        </a>
                        <a href="?<?php echo $_SERVER['QUERY_STRING']; ?>&export=excel" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                </form>
            </section>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <!-- Monthly Trend Chart -->
                <div class="chart-card">
                    <div class="chart-header">Tren Keuangan Bulanan</div>
                    <div class="chart-container">
                        <canvas id="financeChart"></canvas>
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="chart-card">
                    <div class="chart-header">Breakdown Kategori</div>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Transaction Detail Table -->
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Detail Transaksi</div>
                </div>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Transaksi</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Diinput Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($detail_result && mysqli_num_rows($detail_result) > 0):
                                $no = 1;
                                while($row = mysqli_fetch_assoc($detail_result)):
                                    $amount_class = $row['jenis'] == 'pemasukan' ? 'amount-in' : 'amount-out';
                                    
                                    // Category badge class mapping
                                    $category_classes = [
                                        'Iuran Warga' => 'iuran',
                                        'Bantuan Pemerintah' => 'bantuan',
                                        'Operasional' => 'operasional',
                                        'Kegiatan' => 'kegiatan',
                                        'Pemeliharaan' => 'pemeliharaan'
                                    ];
                                    $category_class = isset($category_classes[$row['kategori']]) ? $category_classes[$row['kategori']] : 'iuran';
                                    
                                    // Generate ID Transaksi - DIPERBAIKI: Hilangkan parameter jenis
                                    $id_transaksi = generateTransaksiId($row['id'], $row['tanggal']);
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <span class="transaksi-id"><?php echo $id_transaksi; ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $row['jenis']; ?>">
                                        <?php 
                                        if($row['jenis'] == 'pemasukan') {
                                            echo '<i class="fas fa-arrow-up"></i> Pemasukan';
                                        } else {
                                            echo '<i class="fas fa-arrow-down"></i> Pengeluaran';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="category-badge <?php echo $category_class; ?>">
                                        <?php echo $row['kategori']; ?>
                                    </span>
                                </td>
                                <td class="<?php echo $amount_class; ?>">
                                    Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['user_nama'] ?: 'System'); ?></td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px;">
                                    <i class="fas fa-inbox" style="font-size: 3rem; color: #d1d5db; margin-bottom: 10px;"></i>
                                    <p>Tidak ada transaksi dalam periode ini</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
            
            submenu.classList.toggle('open');
            arrow.classList.toggle('open');
        }

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Monthly Finance Chart
            const financeCtx = document.getElementById('financeChart').getContext('2d');
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
                                        return 'Rp ' + (value / 1000000).toFixed(1) + ' jt';
                                    } else if (value >= 1000) {
                                        return 'Rp ' + (value / 1000).toFixed(0) + ' rb';
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

            // Category Chart - Doughnut chart showing income vs expense
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pemasukan', 'Pengeluaran'],
                    datasets: [{
                        data: [<?php echo $stats['total_pemasukan']; ?>, <?php echo $stats['total_pengeluaran']; ?>],
                        backgroundColor: [
                            '#059669',
                            '#ef4444'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed);
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
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
    </script>
</body>
</html>