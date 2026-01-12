<?php
if (!isset($GLOBALS['__wheel_view'])) {
    require_once __DIR__ . '/../app_init.php';
}
// Lấy thông tin User để hiển thị trên Menu
require_once __DIR__ . '/../auth_functions.php';
$h_currentUser = getCurrentUser();
$h_isLoggedIn = ($h_currentUser !== null);

$title = view_get('title', 'Bếp Nhà Myn');
$bodyClass = view_get('bodyClass', '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css?v=9.0" rel="stylesheet">
</head>
<body class="<?= e($bodyClass) ?> kitchen-theme">
    
    <?php if ($bodyClass !== 'bg-login'): ?>
        <div class="kitchen-header-deco">...</div> 
    <?php endif; ?>

    <div class="container">
        
        <?php if ($bodyClass !== 'bg-login'): ?>
        <div class="tet-card navbar-card">
            <div class="navbar-header">
                <h3 class="m-0 fw-bold d-flex align-items-center">
                    <a href="index.php" class="text-white text-decoration-none">
                        <i class="fas fa-fire-burner me-2"></i>
                        <span class="d-none d-sm-inline">Bếp Nhà Myn</span>
                    </a>
                </h3>

                <div class="nav-btn-group d-flex align-items-center gap-2">
                    <?php if (!$h_isLoggedIn): ?>
                        <a href="login.php" class="btn btn-sm btn-light text-danger">
                            <i class="fas fa-key"></i> <span class="d-none d-md-inline">Đăng Nhập</span>
                        </a>
                        <a href="register.php" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-user-plus"></i> <span class="d-none d-md-inline">Đăng Ký</span>
                        </a>
                    <?php else: ?>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle text-dark" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-hat-chef text-danger me-1"></i> 
                                <span class="d-none d-md-inline fw-bold"><?= htmlspecialchars($h_currentUser['username']) ?></span>
                            </button>
                            <ul class="dropdown-menu shadow border-0 mt-2" style="border-radius: 12px;">
                                <li>
                                    <a class="dropdown-item py-2" href="index.php">
                                        <i class="fas fa-home me-2 text-primary"></i> Phòng Khách
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="manage_dishes.php">
                                        <i class="fas fa-utensils me-2 text-success"></i> Vào Bếp Nấu
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item py-2 text-danger" href="logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i> Đăng Xuất
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>