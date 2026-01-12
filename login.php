<?php
session_start();
require_once 'auth_functions.php';

// Nếu đã đăng nhập rồi thì về trang chủ
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $message = '<div class="alert alert-danger">Vui lòng nhập đủ thông tin!</div>';
    } else {
        $result = loginUser($username, $password);
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['username'] = $result['username'];
            $_SESSION['full_name'] = $result['full_name'];
            header("Location: index.php");
            exit;
        } else {
            $message = '<div class="alert alert-danger">' . $result['message'] . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Bếp Nhà Myn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css?v=5.0" rel="stylesheet">
</head>
<body class="bg-login"> 
    <div class="container d-flex justify-content-center">
        <div class="app-card p-4 shadow-lg" style="width: 100%; max-width: 400px;">
            
            <div class="text-center mb-4">
                <div class="d-inline-block p-3 rounded-circle mb-3" style="background: var(--bg-body);">
                    <i class="fas fa-fire-burner fa-3x" style="color: var(--primary);"></i>
                </div>
                <h3 class="fw-bold" style="color: var(--primary);">Bếp Nhà Myn 💗</h3>
                <p class="text-muted small">Đăng nhập để vào bếp quản lý món ăn</p>
            </div>
            
            <?php echo $message; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">TÊN ĐĂNG NHẬP</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" name="username" class="form-control border-start-0 ps-0" 
                               placeholder="username" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted">MẬT KHẨU</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0" 
                               placeholder="••••••" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary-action mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i> Vào Bếp
                </button>
            </form>
            
            <div class="text-center pt-3 border-top">
                <p class="mb-0 text-muted">Chưa có chìa khóa?</p>
                <a href="register.php" class="fw-bold text-decoration-none" style="color: var(--primary);">
                    Đăng ký ngay
                </a>
                
                <div class="mt-3">
                    <a href="index.php" class="text-secondary text-decoration-none small">
                        <i class="fas fa-arrow-left me-1"></i> Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>