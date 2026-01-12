<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Kiểm tra hệ thống</h2>";

// 1. Kiểm tra MySQLi
if (extension_loaded('mysqli')) {
    echo "✅ MySQLi: OK!<br>";
} else {
    echo "❌ MySQLi: CHƯA BẬT!<br>";
    die();
}

// 2. Test kết nối database
echo "<br><h3>Test kết nối Database:</h3>";
$host = 'localhost';
$user = 'phangiac_wheel'; // ⚠️ Thay username thật
$pass = 'Yennhi12@5'; // ⚠️ Thay password thật
$db = 'phangiac_wheel'; // ⚠️ Thay database name thật

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "❌ Lỗi kết nối: " . $conn->connect_error . "<br>";
    echo "⚠️ Kiểm tra lại username, password, database name trong test.php<br>";
} else {
    echo "✅ Kết nối database thành công!<br>";
    echo "Database: <strong>$db</strong><br>";
    
    // 3. Kiểm tra bảng
    echo "<br><h3>Kiểm tra bảng:</h3>";
    
    // Kiểm tra bảng dishes
    $result = $conn->query("SHOW TABLES LIKE 'dishes'");
    if ($result->num_rows > 0) {
        echo "✅ Bảng 'dishes': Tồn tại<br>";
        
        // Đếm số món ăn
        $count = $conn->query("SELECT COUNT(*) as total FROM dishes")->fetch_assoc();
        echo "&nbsp;&nbsp;&nbsp;→ Có <strong>{$count['total']}</strong> món ăn<br>";
    } else {
        echo "❌ Bảng 'dishes': CHƯA TỒN TẠI - Cần chạy file database.sql<br>";
    }
    
    // Kiểm tra bảng menu_history
    $result = $conn->query("SHOW TABLES LIKE 'menu_history'");
    if ($result->num_rows > 0) {
        echo "✅ Bảng 'menu_history': Tồn tại<br>";
    } else {
        echo "❌ Bảng 'menu_history': CHƯA TỒN TẠI - Cần chạy file database.sql<br>";
    }
    
    $conn->close();
}

echo "<br><h3>Test load file:</h3>";

// 4. Kiểm tra file config.php
if (file_exists('config.php')) {
    echo "✅ File config.php: Tồn tại<br>";
    require_once 'config.php';
    echo "✅ Load config.php: Thành công<br>";
} else {
    echo "❌ File config.php: KHÔNG TÌM THẤY<br>";
}

// 5. Kiểm tra file functions.php
if (file_exists('functions.php')) {
    echo "✅ File functions.php: Tồn tại<br>";
    require_once 'functions.php';
    echo "✅ Load functions.php: Thành công<br>";
} else {
    echo "❌ File functions.php: KHÔNG TÌM THẤY<br>";
}

echo "<br><h3>✅ Nếu tất cả đều OK, hãy truy cập <a href='index.php'>index.php</a></h3>";
?>