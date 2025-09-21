<?php
// google-callback.php
session_start();
require_once 'config/koneksi.php';
require_once 'config/google-config.php';

$googleClient = getGoogleClient();

if(isset($_GET['code'])) {
    try {
        // Exchange authorization code for access token
        $token = $googleClient->fetchAccessTokenWithAuthCode($_GET['code']);
        $googleClient->setAccessToken($token['access_token']);
        
        // Get user profile info
        $google_oauth = new Google_Service_Oauth2($googleClient);
        $google_account_info = $google_oauth->userinfo->get();
        
        // Extract user data
        $email = mysqli_real_escape_string($koneksi, $google_account_info->email);
        $name = mysqli_real_escape_string($koneksi, $google_account_info->name);
        $google_id = mysqli_real_escape_string($koneksi, $google_account_info->id);
        $avatar = mysqli_real_escape_string($koneksi, $google_account_info->picture);
        
        // Check if user exists by email
        $check_query = "SELECT * FROM users WHERE email = '$email'";
        $check_result = mysqli_query($koneksi, $check_query);
        
        if(!$check_result) {
            die("Query Error: " . mysqli_error($koneksi));
        }
        
        if(mysqli_num_rows($check_result) > 0) {
            // User exists, login
            $user = mysqli_fetch_assoc($check_result);
            
            // Update Google ID and avatar if empty
            if(empty($user['google_id']) || empty($user['avatar'])) {
                $update_query = "UPDATE users SET 
                                google_id = '$google_id',
                                avatar = '$avatar',
                                updated_at = NOW()
                                WHERE id = " . $user['id'];
                mysqli_query($koneksi, $update_query);
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = $avatar;
            
            // Redirect based on role
            if($user['role'] == 'admin') {
                header("Location: dashboard.php");
                exit();
            } else {
                header("Location: index.php");
                exit();
            }
            
        } else {
            // New user, create account with role 'user'
            $insert_query = "INSERT INTO users (nama, email, google_id, avatar, role, status) 
                           VALUES ('$name', '$email', '$google_id', '$avatar', 'user', 'aktif')";
            
            if(mysqli_query($koneksi, $insert_query)) {
                $user_id = mysqli_insert_id($koneksi);
                
                // Set session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['nama'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'user';
                $_SESSION['avatar'] = $avatar;
                
                // Redirect to index for regular users
                header("Location: index.php?welcome=true");
                exit();
            } else {
                die("Insert Error: " . mysqli_error($koneksi));
            }
        }
        
    } catch(Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: login.php");
    exit();
}
?>