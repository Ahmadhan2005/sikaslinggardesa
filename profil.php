<?php
session_start();
require_once 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data user
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Handle form update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];

    $update_query = "UPDATE users SET nama = ?, email = ?, password = ? WHERE id = ?";
    $stmt = mysqli_prepare($koneksi, $update_query);
    mysqli_stmt_bind_param($stmt, "sssi", $nama, $email, $password, $user_id);
    
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['nama'] = $nama;
        $success = "Profil berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui profil!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil saya - SiKaslinggar</title>
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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 40px;
            color: #059669;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        .profile-title {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .profile-subtitle {
            opacity: 0.9;
            font-size: 1rem;
        }

        .profile-content {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            color: #374151;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .form-text {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 5px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #059669;
            color: white;
        }

        .btn-primary:hover {
            background: #047857;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .invalid-feedback {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
            }
            
            .profile-header {
                padding: 20px;
            }
            
            .profile-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h1 class="profile-title"><?= htmlspecialchars($user['nama']) ?></h1>
                <p class="profile-subtitle"><?= ucfirst($user['role']) ?></p>
            </div>

            <div class="profile-content">
                <?php if(isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" 
                               value="<?= htmlspecialchars($user['nama']) ?>" required>
                        <div class="invalid-feedback">Nama tidak boleh kosong</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                        <div class="invalid-feedback">Email tidak valid</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password" class="form-control" minlength="6">
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> 
                            Kosongkan jika tidak ingin mengubah password
                        </div>
                    </div>

                    <div class="btn-group">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Form validation
    (function() {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>
