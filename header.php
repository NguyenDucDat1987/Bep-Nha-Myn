<?php
$currentScript = basename($_SERVER['PHP_SELF']);
// Lấy thông tin user
if (file_exists('auth_functions.php')) require_once 'auth_functions.php';
elseif (file_exists(__DIR__ . '/auth_functions.php')) require_once __DIR__ . '/auth_functions.php';

$h_currentUser = function_exists('getCurrentUser') ? getCurrentUser() : null;
$h_isLoggedIn = ($h_currentUser !== null);
$title = isset($GLOBALS['view_title']) ? $GLOBALS['view_title'] : 'Bếp Nhà Myn';
$bodyClass = isset($GLOBALS['view_bodyClass']) ? $GLOBALS['view_bodyClass'] : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css?v=17.0" rel="stylesheet">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">

    <div class="container">
        
        <?php if ($bodyClass !== 'bg-login'): ?>
        <div class="navbar-card">
            <div class="navbar-header">
                <a href="index.php" class="navbar-brand text-decoration-none">
                    <i class="fas fa-fire-burner me-2"></i> Bếp Nhà Myn 💗
                </a>

                <div class="d-flex align-items-center gap-2">
                    <?php if (!$h_isLoggedIn): ?>
<a href="register.php" class="nav-btn">
                            <i class="fas fa-user-plus me-1"></i> Đăng Ký
                        </a>
                        
                        <a href="login.php" class="nav-btn">
                            <i class="fas fa-key me-1"></i> Đăng Nhập
                        </a>
                    <?php else: ?>
                        
<div class="d-flex align-items-center me-3">
    <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center bg-white" 
         style="width: 52px; height: 52px; border: 2px solid rgba(255,255,255,0.8);">
        
        <svg width="42" height="42" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 26C16 18 20 12 32 12C44 12 48 18 48 26C52 26 54 28 54 32C54 35 52 37 48 37L16 37C12 37 10 35 10 32C10 28 12 26 16 26Z" fill="#ECEFF1"/>
            <path d="M16 37L48 37L48 42C48 43 47 44 46 44L18 44C17 44 16 43 16 42L16 37Z" fill="#CFD8DC"/>
            <path d="M20 44L44 44L44 50C44 56 38 60 32 60C26 60 20 56 20 50L20 44Z" fill="#FFCCBC"/>
            <path d="M16 50L24 62L40 62L48 50" fill="#FF7043"/>
            <path d="M26 52C26 52 28 50 32 50C36 50 38 52 38 52C38 52 36 54 32 54C28 54 26 52 26 52Z" fill="#5D4037"/>
        </svg>

    </div>
    
    <div class="ms-2 d-flex flex-column justify-content-center">
        <span class="text-white fw-bold" style="font-size: 1rem; line-height: 1.2; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
            <?= htmlspecialchars($h_currentUser['username']) ?>
        </span>
        <span class="badge bg-warning text-dark rounded-pill shadow-sm" style="font-size: 0.65rem; width: fit-content; margin-top: 2px;">
            <i class="fas fa-star me-1" style="font-size: 0.6rem;"></i>Bếp Trưởng
        </span>
    </div>
</div>

                        <?php if ($currentScript === 'index.php'): ?>
                            <a href="manage_dishes.php" class="nav-btn" style="background: var(--k-white); color: var(--k-primary);">
                                <i class="fas fa-utensils me-1"></i> Vào Bếp
                            </a>
                        <?php else: ?>
                            <a href="index.php" class="nav-btn">
                                <i class="fas fa-home me-1"></i> Phòng Khách
                            </a>
                        <?php endif; ?>

                        <a href="logout.php" class="nav-btn" style="background: rgba(255,255,255,0.15);">
                            <i class="fas fa-sign-out-alt me-1"></i> Thoát
                        </a>

                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>