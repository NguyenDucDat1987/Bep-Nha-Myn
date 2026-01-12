<?php
session_start();
require_once 'auth_functions.php';

if (isLoggedIn()) { header("Location: index.php"); exit; }

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($username) || empty($password)) {
        $message = '<div class="alert alert-danger">Vui lòng điền đủ thông tin!</div>';
    } elseif ($password !== $confirmPassword) {
        $message = '<div class="alert alert-danger">Mật khẩu xác nhận không khớp!</div>';
    } else {
        $result = registerUser($username, $password, $fullName, $email);
        if ($result['success']) {
            $message = '<div class="alert alert-success">' . $result['message'] . ' <a href="login.php" class="fw-bold">Đăng nhập ngay</a></div>';
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
    <title>Đăng Ký - Bếp Nhà Myn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css?v=5.0" rel="stylesheet">
</head>
<body class="bg-login">
    
    <div class="container d-flex justify-content-center my-5">
        <div class="app-card p-4 shadow-lg" style="width: 100%; max-width: 450px;">
            
            <div class="text-center mb-4">
                <h3 class="fw-bold" style="color: var(--secondary);">Đăng Ký Thành Viên</h3>
                <p class="text-muted small">Tạo tài khoản để lưu thực đơn riêng</p>
            </div>
            
            <?php echo $message; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold small text-muted">USERNAME <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Viết liền không dấu" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">HỌ VÀ TÊN</label>
                    <input type="text" name="full_name" class="form-control" placeholder="VD: Mẹ Myn">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">EMAIL (Tùy chọn)</label>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">MẬT KHẨU <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted">XÁC NHẬN MẬT KHẨU <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu trên" required>
                </div>
                
                <button type="submit" class="btn btn-primary-action mb-3" style="background: var(--secondary); border-color: #388e3c;">
                    <i class="fas fa-user-plus me-2"></i> Tạo Tài Khoản
                </button>
            </form>
            
            <div class="text-center pt-3 border-top">
                <p class="mb-0 text-muted">Đã là thành viên?</p>
                <a href="login.php" class="fw-bold text-decoration-none" style="color: var(--secondary);">
                    Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>

</body>
</html>