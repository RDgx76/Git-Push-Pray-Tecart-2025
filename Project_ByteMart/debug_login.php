<?php
session_start();
require_once 'db.php';

echo "<h2>Debug Login System</h2>";

// Cek koneksi database
echo "<h3>1. Database Connection:</h3>";
if ($conn) {
    echo "✅ Connected<br>";
    
    // Cek tabel user
    $sql = "SELECT * FROM user";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        echo "✅ User table has data<br>";
        
        echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Password</th><th>Role</th></tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id_user'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['password'] . "</td>";
            echo "<td>" . $row['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test password
        echo "<h3>2. Password Test:</h3>";
        echo "MD5('admin123') = " . md5('admin123') . "<br>";
        
        $sql = "SELECT MD5('admin123') as hash";
        $r = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($r);
        echo "MySQL MD5('admin123') = " . $row['hash'] . "<br>";
        
    } else {
        echo "❌ User table empty<br>";
    }
} else {
    echo "❌ Not connected: " . mysqli_connect_error();
}

echo "<h3>3. Try Manual Login:</h3>";
?>
<form method="POST" action="index.php">
    Username: <input type="text" name="username" value="admin"><br>
    Password: <input type="password" name="password" value="admin123"><br>
    <input type="submit" name="login" value="Test Login">
</form>