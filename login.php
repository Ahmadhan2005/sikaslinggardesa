<?php
session_start();
require_once 'config/koneksi.php';
require_once 'config/google-config.php';

// Redirect jika user sudah login
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Tampilkan pesan logout sukses
if(isset($_GET['logout']) && $_GET['logout'] == 'true') {
    $success = "Anda telah berhasil logout dari sistem.";
}

// Generate Google Login URL
$googleClient = getGoogleClient();
$googleLoginUrl = $googleClient->createAuthUrl();

// Di bagian atas login.php
if(isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Di bagian proses login regular
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];
    
    // Query menggunakan email
    $query = "SELECT * FROM users WHERE email = '$email' AND status = 'aktif'";
    $result = mysqli_query($koneksi, $query);
    
    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if(password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if($user['role'] == 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar atau akun tidak aktif!";
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SiKaslinggar</title>
    <link rel="shortcut icon" href="assets/img/logokabupaten.png" type="image/png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #059669;
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5,150,105,0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(5,150,105,0.3);
        }
        
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: #666;
        }
        
        .google-login-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px;
            background: #4285f4;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .google-login-btn:hover {
            background: #357ae8;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(66,133,244,0.3);
        }
        
        .google-login-btn img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            background: white;
            padding: 2px;
            border-radius: 3px;
        }
        
        .error-msg {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-msg {
            background: #e1f5fe;
            color: #01579b;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .register-link a {
            color: #059669;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        /* Tambahkan style untuk tombol kembali */
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Tambahkan tombol kembali -->
    <a href="index.php" class="back-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
        </svg>
        Kembali
    </a>

    <div class="login-container">
        <?php if(isset($success)): ?>
            <div class="success-msg">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="login-header">
            <h2>SiKaslinggar</h2>
            <p>Sistem Informasi Kas Desa Linggar</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="Masukkan email">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Masukkan password">
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        
        
        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar disini</a>
        </div>
    </div>
</body>
</html>