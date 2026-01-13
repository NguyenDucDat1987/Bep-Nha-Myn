<?php
// index.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/app_init.php';
require_once 'functions.php';
require_once 'auth_functions.php';

// Cấu hình View
$GLOBALS['view_title'] = 'Bếp Nhà Myn - Hôm Nay Ăn Gì?';
$GLOBALS['view_bodyClass'] = '';

// Kiểm tra User hay Khách
$currentUser = getCurrentUser();
$userId = $currentUser['id'] ?? null;
$isGuest = ($userId === null);

$message = '';
$selectedDishes = [];
$fireworks = false;

// XỬ LÝ QUAY MÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isSpin = isset($_POST['spin']);

    if ($isSpin) {
        $dayOfWeek = $_POST['day_of_week'] ?? '';
        $results = [];
        $hasSuccess = false;

        foreach (['man', 'rau', 'canh'] as $category) {
            if (!$isGuest && hasDishForDay($dayOfWeek, $category, $userId)) {
                $results[] = "⚠️ " . getCategoryName($category) . " đã có rồi";
            } else {
                $availableDishes = getAvailableDishes($dayOfWeek, $category, $userId);

                if (empty($availableDishes)) {
                    $results[] = "❌ Hết " . getCategoryName($category) . " để chọn";
                } else {
                    $randomIndex = array_rand($availableDishes);
                    $dish = $availableDishes[$randomIndex];

                    if (!$isGuest) {
                        saveDishSelection($dish['id'], $dayOfWeek, $category, $userId);
                    }

                    $selectedDishes[$category] = $dish;
                    $results[] = "✅ " . getCategoryName($category) . ": " . $dish['name'];
                    $hasSuccess = true;
                }
            }
        }

        $msgTitle = $isGuest ? "🎉 Gợi ý (Dùng thử):" : "🎉 Thực đơn hôm nay:";
        $bgClass = $hasSuccess ? 'alert-success' : 'alert-warning';
        $message = '<div class="alert ' . $bgClass . ' shadow-sm"><strong>' . $msgTitle . '</strong><br>' . implode('<br>', $results) . '</div>';

        if ($isGuest) {
            $message .= '<div class="alert alert-warning small mt-2 shadow-sm">💡 Bạn đang dùng thử. Hãy <a href="login.php" class="fw-bold text-dark">Đăng nhập</a> để lưu thực đơn nhé!</div>';
        }
        if ($hasSuccess)
            $fireworks = true;
    }
}

// Lấy lịch sử thực đơn tuần
$weekMenu = $userId ? getWeekMenu($userId) : [];
$dayMapping = getDayMapping();
$menuByDay = [];
foreach ($weekMenu as $item) {
    $menuByDay[$item['day_of_week']][$item['category']] = $item;
}
?>

<?php require_once 'header.php'; ?>

<?php echo $message; ?>

<div class="app-card">
    <div class="text-center mb-4">
        <h4 style="color: var(--k-primary); font-weight: 800;">
            🤔 Hôm nay ăn gì nhỉ?
        </h4>
        <p class="text-muted small">Chọn ngày và để Bếp Myn gợi ý nhé</p>
    </div>

    <form method="POST">
        <?php if (function_exists('csrf_field'))
            echo csrf_field(); ?>

        <div class="mb-4">
            <select name="day_of_week" class="form-select form-select-lg" required>
                <option value="">-- Chọn ngày nấu --</option>
                <?php foreach ($dayMapping as $key => $value): ?>
                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" name="spin" class="btn btn-spin">
                <i class="fas fa-wand-magic-sparkles me-2"></i> GỢI Ý THỰC ĐƠN
            </button>

            <?php if (!$isGuest): ?>
                <button type="button" id="btn-reset-week" class="btn btn-reset">
                    <i class="fas fa-trash-can me-2"></i> Làm Mới Thực Đơn Tuần
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (!empty($selectedDishes)): ?>
    <div class="app-card">
        <h5 class="text-center mb-4" style="color: var(--k-secondary); font-weight: 700;">
            <i class="fas fa-utensils me-2"></i>Món ngon đã chọn:
        </h5>
        <div class="row">
            <?php foreach ($selectedDishes as $cat => $dish): ?>
                <div class="col-md-4 mb-3">
                    <div class="selected-dish-card">
                        <span class="badge badge-<?php echo $cat; ?> mb-2"><?php echo getCategoryName($cat); ?></span>
                        <h6 class="mb-2 fw-bold dish-name">
                            <?php echo htmlspecialchars($dish['name']); ?>
                        </h6>
                        <small class="text-muted"><?php echo nl2br(htmlspecialchars($dish['description'])); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!$isGuest && !empty($weekMenu)): ?>
    <div class="app-card">
        <h5 class="text-center mb-4" style="color: var(--k-primary); font-weight: 700;">
            <i class="fas fa-book-open me-2"></i> Sổ Tay Thực Đơn Tuần
        </h5>

        <div class="row g-3">
            <?php foreach ($dayMapping as $dayKey => $dayName): ?>
                <?php if (isset($menuByDay[$dayKey])): ?>
                    <div class="col-md-6">
                        <div class="week-day-card">
                            <div class="day-header">
                                <?php echo $dayName; ?>
                            </div>

                            <?php foreach (['man', 'rau', 'canh'] as $cat): ?>
                                <?php if (isset($menuByDay[$dayKey][$cat])):
                                    $item = $menuByDay[$dayKey][$cat]; ?>
                                    <div class="day-meal-item" id="history-<?php echo $item['id']; ?>">
                                        <div class="meal-info">
                                            <span class="badge badge-<?php echo $cat; ?> mb-1">
                                                <?php echo getCategoryName($cat); ?>
                                            </span>
                                            <div class="meal-name">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </div>
                                        </div>

                                        <button type="button" class="btn-remove" onclick="deleteMenuItem(<?php echo $item['id']; ?>)"
                                            title="Gỡ món này">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($fireworks): ?>
    <script>window.addEventListener('load', function () { if (typeof triggerFireworks === 'function') triggerFireworks(); });</script>

<?php endif; ?>
<! -- bắt đầu đoạn popup -->
    <?php if ($isGuest): ?>
        <div class="modal fade" id="guestPopup" tabindex="-1" aria-labelledby="guestPopupLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                    <div class="modal-header text-white"
                        style="background: linear-gradient(135deg, var(--k-primary), #E64A19);">
                        <h5 class="modal-title fw-bold" id="guestPopupLabel">
                            <i class="fas fa-bullhorn me-2"></i> Lời ngỏ từ Bếp Nhà Myn
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 text-center">
                        <h5 class="fw-bold mb-3" style="color: var(--k-text);">
                            "Hôm nay ăn gì?" <br> Câu hỏi khó nhất thế giới! 🤯
                        </h5>

                        <p class="text-muted text-start">
                            Xuất phát từ việc "nóc nhà" 🏠 của mình ngày nào cũng xoắn não nghĩ món ăn, mình đã tạo ra chiếc
                            web này để phó mặc cho nhân phẩm.
                        </p>

                        <div class="alert alert-warning border-0 small shadow-sm text-start"
                            style="background-color: #FFF8E1; color: #5D4037;">
                            <i class="fas fa-star text-warning me-1"></i> Để trải nghiệm xịn nhất, bạn hãy <b>Đăng ký / Đăng
                                nhập</b> nhé. Lúc đó bạn sẽ được tự quản lý danh sách món ăn yêu thích của riêng mình thay
                            vì dùng danh sách mặc định.
                        </div>

                        <p class="small text-muted fst-italic mb-4 text-start">
                            * Web vẫn đang hoàn thiện, nếu thấy lỗi gì hãy nhắn mình để mình fix ngay. Cảm ơn bạn đã ghé
                            thăm!
                        </p>

                        <div class="d-grid gap-2">
                            <a href="register.php" class="btn btn-primary-action text-white">
                                <i class="fas fa-user-plus me-2"></i> ĐĂNG KÝ THÀNH VIÊN
                            </a>
                            <a href="login.php" class="btn btn-light border fw-bold" style="color: var(--k-text);">
                                <i class="fas fa-sign-in-alt me-2"></i> ĐĂNG NHẬP
                            </a>
                        </div>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-link text-muted text-decoration-none btn-sm"
                                data-bs-dismiss="modal" style="font-size: 0.85rem;">
                                Mình chỉ muốn xem thử thôi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Chỉ hiện Popup 1 lần duy nhất trong phiên làm việc
                if (!sessionStorage.getItem('popupShown')) {
                    var myModal = new bootstrap.Modal(document.getElementById('guestPopup'));
                    myModal.show();
                    sessionStorage.setItem('popupShown', 'true');
                }
            });
        </script>
    <?php endif; ?>
    <! -- kết thúc đoạn popup -->
        <?php require_once 'footer.php'; ?>