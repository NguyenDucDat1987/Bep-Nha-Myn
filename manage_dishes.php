<?php
// manage_dishes.php
session_start();
require_once __DIR__ . '/app_init.php';
require_once 'functions.php';
require_once 'auth_functions.php';

// Cấu hình View
$GLOBALS['view_title'] = 'Quản Lý Bếp - Bếp Nhà Myn';
$GLOBALS['view_bodyClass'] = ''; 

// 1. Check Login
if (!isLoggedIn()) { header("Location: login.php"); exit; }
$currentUser = getCurrentUser();
if (!$currentUser) { header("Location: login.php"); exit; }
$userId = $currentUser['id'];

$message = '';
$editDish = null;

// XỬ LÝ FORM (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF nếu cần
    if (function_exists('csrf_validate') && !csrf_validate()) {
        $message = '<div class="alert alert-danger">Lỗi bảo mật (CSRF). Vui lòng thử lại.</div>';
    } else {
        // Cập nhật món
        if (isset($_POST['update_dish'])) {
            $result = updateDish($_POST['dish_id'], $_POST['name'], $_POST['description'], $_POST['category'], $userId);
            $message = $result['success'] ? '<div class="alert alert-success">'.$result['message'].'</div>' : '<div class="alert alert-danger">'.$result['message'].'</div>';
        } 
        // Xóa món
        elseif (isset($_POST['delete_dish'])) {
            $result = deleteDish($_POST['dish_id'], $userId);
            $message = $result['success'] ? '<div class="alert alert-success">'.$result['message'].'</div>' : '<div class="alert alert-danger">'.$result['message'].'</div>';
        }
    }
}

// Lấy món cần sửa
if (isset($_GET['edit'])) { 
    $d = getDishById($_GET['edit']);
    if ($d && $d['user_id'] == $userId) {
        $editDish = $d;
    } else {
        $message = '<div class="alert alert-danger">🚫 Món này không phải của bạn!</div>';
    }
}

// Lấy danh sách món
$allDishes = getMyDishes($userId);
$dishesByCategory = ['man' => [], 'rau' => [], 'canh' => []];
foreach ($allDishes as $dish) { 
    $dishesByCategory[$dish['category']][] = $dish; 
}
?>

<?php require_once 'header.php'; ?>
    
    <?php echo $message; ?>
    
    <div class="app-card">
        <div class="card-header-custom">
            <?php echo $editDish ? '<i class="fas fa-pencil-alt me-2"></i> Sửa Món Ăn' : '<i class="fas fa-plus-circle me-2"></i> Thêm Món Mới'; ?>
        </div>
        
        <form id="form-add" method="POST" <?php if(!$editDish) echo 'onsubmit="return false;"'; ?>>
            <?php if(function_exists('csrf_field')) echo csrf_field(); ?>
            
            <?php if ($editDish): ?>
                <input type="hidden" name="dish_id" value="<?php echo $editDish['id']; ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-muted">TÊN MÓN ĂN</label>
                    <input type="text" name="name" class="form-control" 
                           value="<?php echo $editDish ? htmlspecialchars($editDish['name']) : ''; ?>" 
                           placeholder="VD: Thịt kho tàu... (Có gợi ý)" 
                           required 
                           list="dish-suggestions" 
                           autocomplete="off" 
                           oninput="fetchSuggestions(this.value)">
                    <datalist id="dish-suggestions"></datalist>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-muted">LOẠI MÓN</label>
                    <select name="category" class="form-select" required>
                        <option value="man" <?php echo ($editDish && $editDish['category'] == 'man') ? 'selected' : ''; ?>>🍖 Món Mặn</option>
                        <option value="rau" <?php echo ($editDish && $editDish['category'] == 'rau') ? 'selected' : ''; ?>>🥬 Món Rau</option>
                        <option value="canh" <?php echo ($editDish && $editDish['category'] == 'canh') ? 'selected' : ''; ?>>🍲 Món Canh</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold small text-muted">GHI CHÚ / CÔNG THỨC</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Ví dụ: Kho lửa nhỏ 30 phút..."><?php echo $editDish ? htmlspecialchars($editDish['description']) : ''; ?></textarea>
            </div>
            
            <?php if ($editDish): ?>
                <div class="d-flex gap-2">
                    <button type="submit" name="update_dish" class="btn btn-primary-action">
                        <i class="fas fa-save me-2"></i> LƯU THAY ĐỔI
                    </button>
                    <a href="manage_dishes.php" class="btn btn-reset text-center text-decoration-none">Hủy</a>
                </div>
            <?php else: ?>
                <button type="button" onclick="submitAddDish()" class="btn btn-primary-action">
                    <i class="fas fa-plus me-2"></i> THÊM VÀO BẾP
                </button>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="row">
        <?php 
        $categories = [
            'man' => ['title' => '🍖 Món Mặn', 'class' => 'category-header-man'],
            'rau' => ['title' => '🥬 Món Rau', 'class' => 'category-header-rau'],
            'canh' => ['title' => '🍲 Món Canh', 'class' => 'category-header-canh']
        ];
        
        foreach ($categories as $cat => $info): 
        ?>
        <div class="col-lg-4 mb-4">
            <div class="app-card h-100 p-0 overflow-hidden">
                <div class="p-3 text-white fw-bold text-center <?php echo $info['class']; ?>">
                    <?php echo $info['title']; ?> (<?php echo count($dishesByCategory[$cat]); ?>)
                </div>
                
                <div class="p-3">
                    <div id="list-<?php echo $cat; ?>"> 
                        <?php if (empty($dishesByCategory[$cat])): ?>
                            <div class="text-center text-muted fst-italic py-3 small">Chưa có món nào.</div>
                        <?php else: ?>
                            <?php foreach ($dishesByCategory[$cat] as $dish): ?>
                                <div class="dish-item mb-3" id="dish-<?php echo $dish['id']; ?>">
                                    <div class="meal-card">
                                        <h6 class="dish-name">
                                            <?php echo htmlspecialchars($dish['name']); ?>
                                        </h6>
                                        <small class="text-muted d-block mb-2 text-truncate">
                                            <?php echo $dish['description'] ? htmlspecialchars($dish['description']) : 'Không có mô tả'; ?>
                                        </small>
                                        
                                        <div class="d-flex justify-content-end gap-2 mt-2 pt-2 border-top">
                                            <a href="?edit=<?php echo $dish['id']; ?>" class="btn btn-sm btn-light text-warning border fw-bold" title="Sửa">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <form method="POST" style="display:inline;" onsubmit="return confirmDeleteDish();">
                                                <?php if(function_exists('csrf_field')) echo csrf_field(); ?>
                                                <input type="hidden" name="dish_id" value="<?php echo $dish['id']; ?>">
                                                <button type="submit" name="delete_dish" class="btn btn-sm btn-light text-danger border fw-bold" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

<?php require_once 'footer.php'; ?>