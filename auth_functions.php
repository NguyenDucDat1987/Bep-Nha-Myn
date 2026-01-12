<?php
// File: auth_functions.php
require_once 'config.php';

// Đăng ký tài khoản mới
function registerUser($username, $password, $fullName = '', $email = '') {
    $conn = getConnection();
    
    // Validate
    if (strlen($username) < 3) {
        return ['success' => false, 'message' => 'Username phải có ít nhất 3 ký tự!'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự!'];
    }
    
    // Kiểm tra username đã tồn tại
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows > 0) {
        $checkStmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Username đã tồn tại!'];
    }
    $checkStmt->close();
    
    // Mã hóa password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashedPassword, $fullName, $email);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Đăng ký thành công!', 'user_id' => $userId];
    } else {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        error_log('Wheel registerUser failed: ' . $error);
        return ['success' => false, 'message' => 'Không thể đăng ký lúc này. Vui lòng thử lại sau.'];
    }
}

// Đăng nhập
function loginUser($username, $password) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, username, password, full_name FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Thông tin đăng nhập không chính xác!'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        return [
            'success' => true,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name']
        ];
    } else {
        return ['success' => false, 'message' => 'Thông tin đăng nhập không chính xác!'];
    }
}

// Kiểm tra đã đăng nhập chưa
function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

// Lấy thông tin user hiện tại
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? ''
    ];
}

// Đăng xuất
function logoutUser() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
    session_start();
    session_regenerate_id(true);
}

// Yêu cầu đăng nhập (redirect nếu chưa login)
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}
?>