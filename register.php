<?php
session_start();
require_once 'config/koneksi.php';

// Jika sudah login, redirect ke dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi form
    if (empty($nama) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek apakah username sudah digunakan
        $check_username = "SELECT id FROM users WHERE username = ?";
        $stmt_username = mysqli_prepare($koneksi, $check_username);
        mysqli_stmt_bind_param($stmt_username, "s", $username);
        mysqli_stmt_execute($stmt_username);
        $result_username = mysqli_stmt_get_result($stmt_username);
        
        // Cek apakah email sudah digunakan
        $check_email = "SELECT id FROM users WHERE email = ?";
        $stmt_email = mysqli_prepare($koneksi, $check_email);
        mysqli_stmt_bind_param($stmt_email, "s", $email);
        mysqli_stmt_execute($stmt_email);
        $result_email = mysqli_stmt_get_result($stmt_email);
        
        if (mysqli_num_rows($result_username) > 0) {
            $error = 'Username sudah digunakan!';
        } elseif (mysqli_num_rows($result_email) > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru ke database menggunakan prepared statement
            $insert_query = "INSERT INTO users (nama, email, username, password, role, status, created_at) VALUES (?, ?, ?, ?, 'user', 'aktif', NOW())";
            $stmt_insert = mysqli_prepare($koneksi, $insert_query);
            mysqli_stmt_bind_param($stmt_insert, "ssss", $nama, $email, $username, $hashed_password);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                // Get user ID yang baru dibuat
                $user_id = mysqli_insert_id($koneksi);
                
                // Log aktivitas registrasi
                $activity = "User baru terdaftar: $nama ($username)";
                $log_query = "INSERT INTO activity_log (user_id, activity, created_at) VALUES (?, ?, NOW())";
                $stmt_log = mysqli_prepare($koneksi, $log_query);
                mysqli_stmt_bind_param($stmt_log, "is", $user_id, $activity);
                mysqli_stmt_execute($stmt_log);
                
                // OPSI 1: Redirect ke login dengan pesan sukses (Direkomendasikan)
                $_SESSION['registration_success'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
                $_SESSION['registered_email'] = $email;
                header('Location: login.php');
                exit();
                
            } else {
                $error = 'Gagal mendaftarkan akun. Silakan coba lagi! Error: ' . mysqli_error($koneksi);
            }
            
            // Tutup prepared statements
            mysqli_stmt_close($stmt_insert);
            if(isset($stmt_log)) {
                mysqli_stmt_close($stmt_log);
            }
        }
        
        // Tutup prepared statements untuk pengecekan
        mysqli_stmt_close($stmt_username);
        mysqli_stmt_close($stmt_email);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SiKaslinggar</title>
    <link rel="shortcut icon" href="assets/img/logokabupaten.png" type="image/png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 15px;
        }

        /* Animated background circles */
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .bg-circle:nth-child(1) {
            width: 150px;
            height: 150px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .bg-circle:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 60%;
            right: 10%;
            animation-delay: 5s;
        }

        .bg-circle:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: 10s;
        }

        .bg-circle:nth-child(4) {
            width: 80px;
            height: 80px;
            top: 20%;
            right: 30%;
            animation-delay: 15s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.1;
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 0.2;
            }
        }

        .register-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 380px;
            position: relative;
            z-index: 10;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 6px 15px rgba(5, 150, 105, 0.3);
        }

        .logo i {
            font-size: 28px;
            color: white;
        }

        .register-header h1 {
            color: #1a202c;
            font-size: 1.6rem;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .register-header p {
            color: #718096;
            font-size: 0.85rem;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #4a5568;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f7fafc;
        }

        .form-group input:focus {
            outline: none;
            border-color: #059669;
            background: white;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.85rem;
            border-left: 3px solid #dc2626;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-message i {
            font-size: 1rem;
        }

        .success-message {
            background: #f0fdf4;
            color: #059669;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.85rem;
            border-left: 3px solid #059669;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .success-message i {
            font-size: 1rem;
        }

        .back-button {
            position: fixed;
            top: 15px;
            left: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .register-btn {
            width: 100%;
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .register-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(5, 150, 105, 0.4);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            background: white;
            padding: 0 12px;
            color: #a0aec0;
            position: relative;
            font-size: 0.85rem;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #4a5568;
            font-size: 0.85rem;
        }

        .login-link a {
            color: #059669;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #047857;
            text-decoration: underline;
        }

        /* Show/Hide password toggle */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            transition: color 0.3s;
            font-size: 0.85rem;
        }

        .password-toggle:hover {
            color: #059669;
        }

        /* Loading state */
        .register-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .register-container {
                padding: 20px;
                max-width: 100%;
            }
            
            .register-header h1 {
                font-size: 1.4rem;
            }

            .form-group input {
                font-size: 13px;
                padding: 9px 12px 9px 35px;
            }

            .back-button {
                top: 10px;
                left: 10px;
                padding: 6px 12px;
                font-size: 0.8rem;
            }
        }

        @media (max-height: 650px) {
            .register-container {
                max-height: 90vh;
                overflow-y: auto;
            }
            
            .register-header {
                margin-bottom: 20px;
            }
            
            .form-group {
                margin-bottom: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Tombol kembali -->
    <a href="login.php" class="back-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
        </svg>
        Kembali
    </a>

    <!-- Background circles -->
    <div class="bg-circle"></div>
    <div class="bg-circle"></div>
    <div class="bg-circle"></div>
    <div class="bg-circle"></div>

    <div class="register-container">
        <div class="register-header">
            <div class="logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Daftar Akun</h1>
            <p>Bergabung dengan SIKASLINGGAR</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="nama" name="nama" required 
                           placeholder="Masukkan nama lengkap"
                           value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" required 
                           placeholder="email@example.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-group">
                    <i class="fas fa-at"></i>
                    <input type="text" id="username" name="username" required 
                           placeholder="Pilih username unik"
                           pattern="[a-zA-Z0-9_]{3,20}"
                           title="Username hanya boleh huruf, angka, dan underscore (3-20 karakter)"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" required
                           placeholder="Minimal 6 karakter">
                    <span class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye" id="password-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Ulangi password">
                    <span class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye" id="confirm-password-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="register-btn" id="submitBtn">
                <i class="fas fa-user-plus"></i>
                <span>Daftar Sekarang</span>
            </button>
        </form>

        <div class="divider">
            <span>atau</span>
        </div>

        <div class="login-link">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(fieldId + '-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Form submission with loading state
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner"></div> <span>Mendaftar...</span>';
        });

        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.error-message, .success-message');
            messages.forEach(function(msg) {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500);
            });
        }, 5000);

        // Validate form client-side
        document.getElementById('registerForm').addEventListener('submit', function(e) {    
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-user-plus"></i> <span>Daftar Sekarang</span>';
                return false;
            }
        });
    </script>
</body>
</html>