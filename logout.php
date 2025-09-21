<?php
session_start();

// Fungsi untuk membersihkan session dan cookie
function clearUserSession() {
    // Hapus semua data session
    $_SESSION = array();

    // Hapus cookie session
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }

    // Hancurkan session
    session_destroy();
}

// Jika ini adalah request AJAX untuk logout
if(isset($_POST['action']) && $_POST['action'] == 'logout') {
    clearUserSession();
    echo json_encode(['success' => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - SiKaslinggar</title>
    <link rel="shortcut icon" href="assets/img/logokabupaten.png" type="image/png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        
        /* Background decorative circles */
        body::before {
            content: '';
            position: absolute;
            top: 10%;
            left: 10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 1;
        }
        
        body::after {
            content: '';
            position: absolute;
            bottom: 10%;
            right: 10%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            z-index: 1;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(2px);
        }
        
        .modal {
            background: #ffffff;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            max-width: 420px;
            width: 90%;
            text-align: center;
            transform: scale(0.8);
            opacity: 0;
            animation: modalShow 0.4s ease-out forwards;
            z-index: 1001;
            border: 1px solid rgba(16, 185, 129, 0.1);
        }
        
        @keyframes modalShow {
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .modal-header {
            margin-bottom: 25px;
        }
        
        .modal-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }
        
        .modal-title {
            font-size: 26px;
            color: #1f2937;
            margin-bottom: 12px;
            font-weight: 600;
        }
        
        .modal-message {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn-yes {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: 2px solid transparent;
        }
        
        .btn-yes:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }
        
        .btn-no {
            background: #ffffff;
            color: #10b981;
            border: 2px solid #10b981;
        }
        
        .btn-no:hover {
            background: #f0fdf4;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2);
            border-color: #059669;
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn:disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #ffffff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 480px) {
            .modal {
                padding: 25px;
                margin: 20px;
                border-radius: 15px;
            }
            
            .modal-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                padding: 16px 28px;
            }
            
            .modal-icon {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }
            
            .modal-title {
                font-size: 22px;
            }
        }
        
        /* Smooth entrance animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                backdrop-filter: blur(0px);
            }
            to {
                opacity: 1;
                backdrop-filter: blur(2px);
            }
        }
        
        .overlay {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>
<body>
    <div class="overlay">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-icon">
                    ⚠️
                </div>
                <h2 class="modal-title">Konfirmasi Logout</h2>
            </div>
            <p class="modal-message">
                Apakah Anda yakin ingin keluar dari sistem? Anda akan diarahkan ke halaman login.
            </p>
            <div class="modal-buttons">
                <button class="btn btn-yes" onclick="handleLogout()">Ya, Logout</button>
                <button class="btn btn-no" onclick="cancelLogout()">Tidak, Kembali</button>
            </div>
        </div>
    </div>

    <script>
        async function handleLogout() {
            try {
                const yesBtn = document.querySelector('.btn-yes');
                const noBtn = document.querySelector('.btn-no');
                
                // Disable buttons
                yesBtn.innerHTML = '<span class="loading-spinner"></span>Logging out...';
                yesBtn.disabled = true;
                noBtn.disabled = true;

                // Kirim request logout
                const response = await fetch('logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                });

                const result = await response.json();
                
                if(result.success) {
                    // Hapus data localStorage jika ada
                    localStorage.clear();
                    
                    // Redirect ke login dengan parameter logout=true
                    window.location.href = 'login.php?logout=true';
                }
            } catch(error) {
                console.error('Logout error:', error);
                alert('Terjadi kesalahan saat logout. Silakan coba lagi.');
            }
        }

        function cancelLogout() {
            history.back();
        }
        
        // Tambahkan event listener untuk tombol ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cancelLogout();
            }
        });
        
        // Animasi masuk saat halaman dimuat
        window.addEventListener('load', function() {
            const modal = document.querySelector('.modal');
            modal.style.animation = 'modalShow 0.4s ease-out forwards';
        });
        
        // Prevent accidental clicks on overlay
        document.querySelector('.overlay').addEventListener('click', function(e) {
            if (e.target === this) {
                cancelLogout();
            }
        });
    </script>
</body>
</html>