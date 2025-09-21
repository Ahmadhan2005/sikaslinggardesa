<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiKaslinggar - Sistem Informasi Kas Desa Linggar</title>
    <link rel="shortcut icon" href="assets/img/logokabupaten.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #ffffff 0%, #f8fdfc 100%);
    min-height: 100vh;
    overflow-x: hidden;
    line-height: 1.6;
}

/* Header */
.header {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    padding: 15px 0;
    box-shadow: 0 4px 20px rgba(5, 150, 105, 0.3);
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    backdrop-filter: blur(10px);
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
}

.logo-section {
    display: flex;
    align-items: center;
    gap: 15px;
}

.hamburger {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 1.5em;
    cursor: pointer;
    padding: 8px;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.hamburger:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.logo {
    font-size: 1.8em;
    font-weight: bold;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.nav-menu {
    display: flex;
    gap: 30px;
    list-style: none;
    align-items: center;
}

.nav-menu a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 8px 16px;
    border-radius: 20px;
    position: relative;
}

.nav-menu a:hover {
    background: rgba(255,255,255,0.2);
}

.login-btn {
    background: rgba(255,255,255,0.2);
    border: 2px solid white;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9em;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.login-btn:hover {
    background: white;
    color: #059669;
    transform: scale(1.05);
}

/* User Menu Styles */
.user-menu {
    display: flex;
    align-items: center;
    gap: 15px;
    cursor: pointer;
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: 2px solid white;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: #059669;
    font-weight: bold;
}

.user-name {
    color: white;
    font-weight: 500;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    background-color: white;
    min-width: 200px;
    box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius: 8px;
    overflow: hidden;
    margin-top: 10px;
}

.dropdown-content a {
    color: #333;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    transition: all 0.3s ease;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.dropdown-content.show {
    display: block !important;
}

/* Hero Section */
.hero {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    text-align: center;
    position: relative;
    background: linear-gradient(135deg, #ffffff 0%, #f8fdfc 100%);
    padding: 120px 0 80px;
}

.hero-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

.hero h1 {
    font-size: 3em;
    color: #059669;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    font-weight: 700;
}

.hero p {
    font-size: 1.2em;
    color: #666;
    margin-bottom: 40px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.cta-button {
    display: inline-block;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    padding: 15px 40px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: bold;
    font-size: 1.1em;
    box-shadow: 0 10px 30px rgba(5, 150, 105, 0.3);
    transition: all 0.3s ease;
}

.cta-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(5, 150, 105, 0.4);
}

/* Features Section */
.features {
    padding: 120px 0;
    background: #f8fdfc;
    position: relative;
    z-index: 1;
}

.section-title {
    text-align: center;
    font-size: 2.5em;
    color: #059669;
    margin-bottom: 60px;
    font-weight: 700;
}

.features-grid {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
    padding: 0 30px;
}

.feature-card {
    background: white;
    padding: 40px 30px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid rgba(5, 150, 105, 0.1);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    border-color: #059669;
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    border-radius: 50%;
    margin: 0 auto 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5em;
    color: white;
    box-shadow: 0 5px 15px rgba(5, 150, 105, 0.3);
}

.feature-card h3 {
    color: #059669;
    margin-bottom: 20px;
    font-size: 1.5em;
    font-weight: 600;
}

.feature-card p {
    color: #4b5563;
    line-height: 1.8;
    font-size: 1rem;
    margin: 0;
}

/* About Section */
.about {
    padding: 80px 0;
    background: white;
}

.about-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

.about-text h2 {
    font-size: 2.5em;
    color: #059669;
    margin-bottom: 30px;
    font-weight: 700;
}

.about-text p {
    color: #666;
    line-height: 1.8;
    margin-bottom: 25px;
    font-size: 1em;
    text-align: justify;
}

.about-visual {
    width: 100%;
    height: 400px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 4em;
    box-shadow: 0 20px 50px rgba(5, 150, 105, 0.3);
    position: relative;
    overflow: hidden;
}

.about-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 20px;
}

/* Location Section */
.location {
    padding: 80px 0;
    background: #f8fdfc;
}

.location-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.location-grid {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 60px;
    align-items: start;
}

.location-info {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
}

.info-card {
    background: white;
    padding: 30px 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid rgba(5, 150, 105, 0.1);
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border-color: #059669;
}

.info-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    border-radius: 50%;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8em;
    color: white;
    box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
}

.info-card h3 {
    color: #059669;
    margin-bottom: 15px;
    font-size: 1.3em;
    font-weight: 600;
}

.info-card p {
    color: #666;
    line-height: 1.6;
    font-size: 0.95em;
    text-align: center;
}

.map-container {
    width: 100%;
}

.map-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 1px solid rgba(5, 150, 105, 0.1);
    transition: all 0.3s ease;
}

.map-card:hover {
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    transform: translateY(-3px);
}

.map-header {
    padding: 25px 30px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    text-align: center;
}

.map-header h3 {
    font-size: 1.5em;
    margin-bottom: 8px;
    font-weight: 600;
}

.map-header p {
    color: rgba(255,255,255,0.9);
    font-size: 0.95em;
    margin: 0;
}

.map-wrapper {
    position: relative;
    width: 100%;
    height: 400px;
}

.map-wrapper iframe {
    border-radius: 0;
    transition: all 0.3s ease;
}

.map-footer {
    padding: 20px 30px;
    background: #f8fdfc;
    text-align: center;
}

.directions-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    padding: 12px 25px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9em;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
}

.directions-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
    background: linear-gradient(135deg, #047857 0%, #065f46 100%);
}

.directions-btn i {
    font-size: 1.1em;
}

/* Footer */
.footer {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
    padding: 60px 0 20px;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    margin-bottom: 40px;
}

.footer-section h3 {
    font-size: 1.5em;
    margin-bottom: 20px;
    color: white;
    font-weight: 600;
}

.footer-section p, .footer-section li {
    color: rgba(255,255,255,0.8);
    line-height: 1.7;
    margin-bottom: 10px;
}

.footer-section ul {
    list-style: none;
}

.footer-section ul li {
    padding: 8px 0;
    transition: all 0.3s ease;
         /* Dropdown now only opens on click, not hover */
         display: none;
    }
.footer-section ul li:hover {
    color: white;
    transform: translateX(5px);
}

.footer-section a {
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-link {
    display: inline-block;
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    text-align: center;
    line-height: 45px;
    font-size: 1.3em;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: white;
    color: #059669;
    transform: translateY(-3px);
}

.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.2);
    padding-top: 20px;
    text-align: center;
    color: rgba(255,255,255,0.8);
}

/* Scroll to top button */
.scroll-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #059669;
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 18px;
    cursor: pointer;
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 1000;
    box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
}

.scroll-to-top.visible {
    opacity: 1;
}

.scroll-to-top:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
}

/* Content animations - faster and smoother */
.fade-in {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease-out;
}

.fade-in.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Slide animations for variety */
.slide-left {
    opacity: 0;
    transform: translateX(-30px);
    transition: all 0.3s ease-out;
}

.slide-left.visible {
    opacity: 1;
    transform: translateX(0);
}

.slide-right {
    opacity: 0;
    transform: translateX(30px);
    transition: all 0.3s ease-out;
}

.slide-right.visible {
    opacity: 1;
    transform: translateX(0);
}

.scale-in {
    opacity: 0;
    transform: scale(0.9);
    transition: all 0.3s ease-out;
}

.scale-in.visible {
    opacity: 1;
    transform: scale(1);
}

/* Mobile Menu Styles */
.mobile-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.mobile-overlay.active {
    opacity: 1;
    visibility: visible;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hamburger {
        display: block;
    }

    .header-content {
        padding: 0 15px;
    }

    .nav-menu {
        position: fixed;
        top: 0;
        left: -100%;
        width: 80%;
        max-width: 300px;
        height: 100vh;
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        flex-direction: column;
        align-items: flex-start;
        padding: 80px 30px 30px;
        gap: 0;
        transition: left 0.3s ease;
        z-index: 1001;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .nav-menu.active {
        left: 0;
    }

    .nav-menu li {
        width: 100%;
        margin-bottom: 10px;
    }

    .nav-menu a {
        display: block;
        padding: 15px 20px;
        border-radius: 10px;
        width: 100%;
        border-left: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .nav-menu a:hover {
        background: rgba(255, 255, 255, 0.1);
        border-left-color: white;
        transform: translateX(5px);
    }

    .login-btn {
        margin-top: 20px;
        display: block;
        text-align: center;
        width: auto;
        max-width: 120px;
        margin-left: auto;
        margin-right: 0;
        padding: 10px 16px;
    }

    .dropdown-content {
        position: fixed;
        right: 15px;
        top: 70px;
        background: white;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        overflow: hidden;
        min-width: 180px;
        z-index: 1002;
    }

    .dropdown-content.show {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    .dropdown-content a {
        color: #333;
        padding: 12px 16px;
        border-left: none;
        font-weight: 500;
    }

    .dropdown-content a:hover {
        background: #f8fdfc;
        color: #059669;
    }

    .hero {
        padding: 100px 0 60px;
    }

    .hero h1 {
        font-size: 2.2em;
    }

    .hero p {
        font-size: 1.1em;
    }

    .logo {
        font-size: 1.5em;
    }

    .about-content {
        grid-template-columns: 1fr;
        gap: 40px;
    }

    .about-text h2 {
        font-size: 2em;
    }

    .section-title {
        font-size: 2em;
    }

    .features {
        padding: 80px 0;
    }

    .features-grid {
        grid-template-columns: 1fr;
        gap: 30px;
        padding: 0 20px;
    }

    .feature-card {
        padding: 30px 20px;
    }

    .footer-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .about-visual {
        height: 300px;
    }

    .location-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }

    .location-info {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .info-card {
        padding: 25px 20px;
    }

    .map-wrapper {
        height: 300px;
    }

    .map-header {
        padding: 20px 25px;
    }

    .map-footer {
        padding: 15px 25px;
    }

    .directions-btn {
        padding: 10px 20px;
        font-size: 0.85em;
    }
}

@media (max-width: 480px) {
    .header-content {
        padding: 0 10px;
    }

    .hero-content {
        padding: 0 15px;
    }

    .nav-menu {
        width: 90%;
        padding: 80px 20px 30px;
    }

    .logo {
        font-size: 1.3em;
    }

    .hamburger {
        font-size: 1.3em;
    }
}
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Header dengan hamburger menu -->
    <header class="header">
        <div class="header-content">
            <div class="logo-section">
                <button class="hamburger" id="hamburger">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="logo">SiKaslinggar</div>
            </div>
            
            <nav>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="#features">Fitur</a></li>
                    <li><a href="#about">Tentang</a></li>
                    <li><a href="#location">Lokasi</a></li>
                    <li><a href="#contact">Kontak</a></li>
                </ul>
            </nav>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="dropdown">
                    <div class="user-menu">
                        <?php if(isset($_SESSION['avatar']) && $_SESSION['avatar']): ?>
                            <img src="<?php echo $_SESSION['avatar']; ?>" alt="Avatar" class="user-avatar">
                        <?php else: ?>
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="user-name"><?php echo $_SESSION['nama']; ?></span>
                    </div>
                    <div class="dropdown-content">
                        <?php if($_SESSION['role'] == 'admin'): ?>
                            <a href="dashboard.php">Dashboard Admin</a>
                            <a href="pengguna.php">Kelola Pengguna</a>
                        <?php endif; ?>
                        <a href="profil.php">Profil Saya</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content fade-in">
            <h1>Selamat Datang di SiKaslinggar</h1>
            <p>Sistem Informasi Kas Linggar - Platform Digital untuk Transparansi Keuangan Desa yang Modern dan Terpercaya</p>
            <a href="#features" class="cta-button">Jelajahi Fitur</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2 class="section-title fade-in">Fitur Unggulan Kami</h2>
        <div class="features-grid">
            <div class="feature-card fade-in">
                <div class="feature-icon">💰</div>
                <h3>Manajemen Kas Desa</h3>
                <p>Kelola pemasukan dan pengeluaran kas desa dengan sistem yang terintegrasi dan mudah digunakan untuk transparansi keuangan.</p>
            </div>
            <div class="feature-card fade-in">
                <div class="feature-icon">📊</div>
                <h3>Laporan Real-time</h3>
                <p>Akses laporan keuangan secara real-time dengan visualisasi data yang jelas dan mudah dipahami oleh masyarakat.</p>
            </div>
            <div class="feature-card fade-in">
                <div class="feature-icon">🔒</div>
                <h3>Keamanan Data</h3>
                <p>Sistem keamanan berlapis untuk melindungi data keuangan desa dengan enkripsi tingkat tinggi dan backup otomatis.</p>
            </div>
            <div class="feature-card fade-in">
                <div class="feature-icon">👥</div>
                <h3>Akses Transparan</h3>
                <p>Masyarakat dapat mengakses informasi keuangan desa secara transparan untuk menciptakan pemerintahan yang akuntabel.</p>
            </div>
            <div class="feature-card fade-in">
                <div class="feature-icon">📱</div>
                <h3>Mobile Friendly</h3>
                <p>Desain responsif yang dapat diakses dari berbagai perangkat untuk kemudahan penggunaan di mana saja.</p>
            </div>
            <div class="feature-card fade-in">
                <div class="feature-icon">⚡</div>
                <h3>Proses Cepat</h3>
                <p>Sistem yang dioptimalkan untuk memberikan performa tinggi dalam pemrosesan data dan pembuatan laporan.</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="about-content">
            <div class="about-text fade-in">
                <h2>Tentang SiKaslinggar</h2>
                <p>SIKASLINGGAR (Sistem Informasi Kas Linggar) adalah platform digital yang dirancang khusus untuk mempermudah pengelolaan dan pelaporan keuangan desa secara transparan dan akuntabel. Sistem ini menjadi sarana resmi pemerintah Desa Linggar dalam menyampaikan informasi terkait pemasukan dan pengeluaran kas desa kepada masyarakat.</p>
                
                <p>Dengan interface yang modern dan user-friendly, SIKASLINGGAR memungkinkan perangkat desa untuk menginput transaksi secara sistematis, membuat laporan otomatis, dan menghindari kesalahan dalam pencatatan. Masyarakat juga dapat ikut mengawasi penggunaan dana desa melalui sistem ini. Desa Linggar terletak di Kecamatan Rancaekek, Kabupaten Bandung, dengan luas wilayah 351,385 ha. Desa ini memiliki posisi strategis yang dilalui dua sungai besar yaitu Cimande dan Cikijing, menjadikannya wilayah yang berpotensi untuk pengembangan berbasis teknologi digital.</p>
            </div>
            <div class="about-image fade-in">
                <div class="about-visual">
                   <img src="assets/img/desalinggar.jpg" alt="Desa Linggar" />
                </div>
            </div>
        </div>
    </section>

     <!-- Location Section -->
    <section class="location" id="location">
        <div class="location-content">
            <h2 class="section-title fade-in">Lokasi Desa Linggar</h2>
            <div class="location-grid">
                <div class="location-info fade-in">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Alamat Lengkap</h3>
                        <p>Desa Linggar, Kecamatan Rancaekek Kabupaten Bandung, Jawa Barat, Indonesia</p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3>Kantor Desa</h3>
                        <p>Jl. Raya Linggar No. 123 Desa Linggar, Rancaekek Bandung, Jawa Barat</p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Jam Operasional</h3>
                        <p>Senin - Jumat: 08:00 - 16:00<br>Sabtu - Minggu: Tutup</p>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3>Kontak</h3>
                        <p>Telepon: (022) 1234-5678<br>Email: info@desalinggar.id</p>
                        
                    </div>
                </div>
                
                <div class="map-container fade-in">
                    <div class="map-card">
                        <div class="map-header">
                            <h3>Peta Lokasi</h3>
                            <p>Kantor Desa Linggar, Kecamatan Rancaekek</p>
                        </div>
                        <div class="map-wrapper">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d626.3392967468129!2d107.79780932048247!3d-6.965639507483052!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68c459f335cdf7%3A0x204ef4cac5655797!2sKantor%20Desa%20Linggar!5e0!3m2!1sid!2sid!4v1756251696745!5m2!1sid!2sid" 
                                width="100%" 
                                height="400" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                        <div class="map-footer">
                            <a href="https://maps.google.com/?q=Kantor+Desa+Linggar" target="_blank" class="directions-btn">
                                <i class="fas fa-directions"></i>
                                Dapatkan Petunjuk Arah
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="footer-content">
            <div class="footer-grid">
                <div class="footer-section fade-in">
                    <h3>SiKaslinggar</h3>
                    <p>Sistem Informasi Kas Desa Linggar yang menghadirkan transparansi dan akuntabilitas dalam pengelolaan keuangan desa.</p>
                    <div class="social-links">
                        <a href="https://www.instagram.com/desalinggar/" class="social-link" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com/@desalinggar" class="social-link" target="_blank">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://linggar.desa.id/" class="social-link" target="_blank">
                            <i class="fas fa-globe"></i>
                        </a>
                        <a href="https://www.tiktok.com/@desalinggar/" class="social-link" target="_blank">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section fade-in">
                    <h3>Layanan</h3>
                    <p>Manajemen Kas Desa, Laporan Keuangan, Monitoring Transparansi, Konsultasi Sistem, Pelatihan Pengguna.</p>
                </div>
                
                <div class="footer-section fade-in">
                    <h3>Pemerintah Desa</h3>
                    <p>Tentang Desa Linggar, Profil Desa, Berita Desa, Galeri, Kontak.</p>
                </div>
                
                <div class="footer-section fade-in">
                    <h3>Kontak</h3>
                   <p>Nomor 0822-1234-5678. Lokasi di Desa Linggar, Kec. Rancaekek. Kabupaten Bandung, Jawa Barat. Buka Setiap Hari Senin - Jumat.</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 SiKaslinggar. Hak Cipta Dilindungi Undang-Undang.</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop">↑</button>

    <script>
        // Loading screen
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loading').classList.add('hidden');
            }, 1000);
        });

        // Mobile Menu Toggle
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');
        const mobileOverlay = document.getElementById('mobileOverlay');

        // User Dropdown Toggle
        const userDropdown = document.querySelector('.dropdown');
        const dropdownContent = document.querySelector('.dropdown-content');
        const userMenu = document.querySelector('.user-menu');

        // Dropdown only opens/closes on click
        function toggleUserDropdown(e) {
            e.stopPropagation();
            dropdownContent.classList.toggle('show');
        }
        function closeUserDropdown() {
            dropdownContent.classList.remove('show');
        }
        if (userMenu) {
            userMenu.addEventListener('click', toggleUserDropdown);
        }
        document.addEventListener('click', function(e) {
            if (dropdownContent && !userDropdown.contains(e.target)) {
                closeUserDropdown();
            }
        });
        if (dropdownContent) {
            const dropdownLinks = dropdownContent.querySelectorAll('a');
            dropdownLinks.forEach(link => {
                link.addEventListener('click', closeUserDropdown);
            });
        }

        function toggleMobileMenu() {
            navMenu.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            
            // Change hamburger icon
            const icon = hamburger.querySelector('i');
            if (navMenu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }

        hamburger.addEventListener('click', toggleMobileMenu);
        mobileOverlay.addEventListener('click', toggleMobileMenu);

        // Close mobile menu when clicking on menu items
        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (navMenu.classList.contains('active')) {
                    toggleMobileMenu();
                }
            });
        });

        // Smooth scrolling untuk navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Scroll to top functionality
        const scrollToTopBtn = document.getElementById('scrollToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });

        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Intersection Observer untuk animasi fade-in
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe semua elemen dengan class fade-in
        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Header transparency on scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 50) {
                header.style.background = 'linear-gradient(135deg, rgba(5, 150, 105, 0.95) 0%, rgba(4, 120, 87, 0.95) 100%)';
                header.style.backdropFilter = 'blur(15px)';
            } else {
                header.style.background = 'linear-gradient(135deg, #059669 0%, #047857 100%)';
                header.style.backdropFilter = 'blur(10px)';
            }
        });

        // Close mobile menu on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && navMenu.classList.contains('active')) {
                toggleMobileMenu();
            }
        });

        // Prevent body scroll when mobile menu is open
        function toggleBodyScroll(disable) {
            if (disable) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        // Update mobile menu toggle to handle body scroll
        const originalToggleMobileMenu = toggleMobileMenu;
        toggleMobileMenu = function() {
            originalToggleMobileMenu();
            toggleBodyScroll(navMenu.classList.contains('active'));
        };

        // Dynamic year in footer
        const currentYear = new Date().getFullYear();
        const footerText = document.querySelector('.footer-bottom p');
        if (footerText) {
            footerText.innerHTML = footerText.innerHTML.replace('2025', currentYear);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth transitions to all interactive elements
            const interactiveElements = document.querySelectorAll('a, button, .feature-card');
            interactiveElements.forEach(el => {
                if (!el.style.transition) {
                    el.style.transition = 'all 0.3s ease';
                }
            });

            // Initialize fade-in animations
            const fadeElements = document.querySelectorAll('.fade-in');
            fadeElements.forEach((el, index) => {
                el.style.transitionDelay = `${index * 0.1}s`;
            });
        });

        // Keyboard navigation support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (navMenu.classList.contains('active')) {
                    toggleMobileMenu();
                }
                if (dropdownContent && dropdownContent.classList.contains('show')) {
                    closeUserDropdown();
                }
            }
        });

        // Touch swipe to close mobile menu
        let touchStartX = 0;
        let touchEndX = 0;

        navMenu.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });

        navMenu.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            if (touchEndX < touchStartX - 50) {
                // Swipe left to close menu
                if (navMenu.classList.contains('active')) {
                    toggleMobileMenu();
                }
            }
        }
    </script>
</body>
</html>