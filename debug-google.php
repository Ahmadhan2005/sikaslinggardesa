<?php
// debug-google.php
session_start();
require_once 'config/koneksi.php';

echo "<h2>Debug Google Login</h2>";

// Cek session
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Cek database connection
echo "<h3>Database Connection:</h3>";
if($koneksi) {
    echo "✅ Connected to database<br>";
} else {
    echo "❌ Database connection failed<br>";
}

// Cek tabel users
echo "<h3>Users Table Structure:</h3>";
$result = mysqli_query($koneksi, "DESCRIBE users");
if($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        foreach($row as $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . mysqli_error($koneksi);
}

// Cek data users
echo "<h3>Users Data:</h3>";
$result = mysqli_query($koneksi, "SELECT id, nama, email, role, status, google_id FROM users");
if($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Google ID</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        foreach($row as $value) {
            echo "<td>" . ($value ?: '-') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . mysqli_error($koneksi);
}

// Test Google Config
echo "<h3>Google Config:</h3>";
require_once 'config/google-config.php';
echo "Client ID: " . (defined('GOOGLE_CLIENT_ID') ? "✅ Set" : "❌ Not Set") . "<br>";
echo "Client Secret: " . (defined('GOOGLE_CLIENT_SECRET') ? "✅ Set" : "❌ Not Set") . "<br>";
echo "Redirect URI: " . (defined('GOOGLE_REDIRECT_URI') ? GOOGLE_REDIRECT_URI : "Not Set") . "<br>";

// Test Google Client
try {
    $client = getGoogleClient();
    echo "✅ Google Client initialized<br>";
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>