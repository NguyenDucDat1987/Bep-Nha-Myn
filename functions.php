<?php
require_once 'config.php';

// =========================================================
// 1. CÁC HÀM LẤY DỮ LIỆU (READ)
// =========================================================

// Hàm lấy các món khả dụng để quay
// ✅ AN TOÀN: Lấy TẤT CẢ món (Guest + User đều thấy tất cả)
function getAvailableDishes($dayOfWeek, $category, $userId = null) {
    $conn = getConnection();
    $weekNumber = getWeekNumber();
    $year = getYear();
    
    // 1. Lấy danh sách ID các món đã ăn trong tuần (chỉ nếu có userId)
    $usedDishIds = [];
    if ($userId) {
        $sqlHistory = "SELECT dish_id FROM menu_history WHERE user_id = ? AND week_number = ? AND year = ? AND category = ?";
        $stmtH = $conn->prepare($sqlHistory);
        $stmtH->bind_param("iiis", $userId, $weekNumber, $year, $category);
        $stmtH->execute();
        $resH = $stmtH->get_result();
        while ($row = $resH->fetch_assoc()) {
            $usedDishIds[] = $row['dish_id'];
        }
        $stmtH->close();
    }

    // 2. Lấy TẤT CẢ món (không phân biệt user_id)
    // ✅ Tất cả user + guest đều thấy toàn bộ món
    $sql = "SELECT * FROM dishes WHERE category = ?";
    $params = [$category];
    $types = "s";

    // Loại trừ món đã ăn trong tuần (chỉ áp dụng cho user đã đăng nhập)
    if (!empty($usedDishIds)) {
        $placeholders = implode(',', array_fill(0, count($usedDishIds), '?'));
        $sql .= " AND id NOT IN ($placeholders)";
        $types .= str_repeat('i', count($usedDishIds));
        $params = array_merge($params, $usedDishIds);
    }

    $sql .= " ORDER BY name";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $dishes = [];
    while ($row = $result->fetch_assoc()) {
        $dishes[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $dishes;
}

// Hàm lấy danh sách món RIÊNG của User (để quản lý)
// ✅ Chỉ hiển thị món của chính user để SỬA/XÓA
function getMyDishes($userId) {
    $conn = getConnection();
    // Chỉ lấy món do chính user này tạo
    $sql = "SELECT * FROM dishes WHERE user_id = ? ORDER BY category, name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $dishes = [];
    while ($row = $result->fetch_assoc()) {
        $dishes[] = $row;
    }
    $stmt->close();
    $conn->close();
    return $dishes;
}

// Hàm lấy thực đơn tuần của User
function getWeekMenu($userId) {
    if (!$userId) return [];

    $conn = getConnection();
    $weekNumber = getWeekNumber();
    $year = getYear();
    
    $sql = "SELECT mh.id, mh.day_of_week, mh.category, d.name, d.description, d.created_at, mh.selected_at
            FROM menu_history mh
            JOIN dishes d ON mh.dish_id = d.id
            WHERE mh.user_id = ? AND mh.week_number = ? AND mh.year = ?
            ORDER BY FIELD(mh.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
                     FIELD(mh.category, 'man', 'rau', 'canh')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $userId, $weekNumber, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $menu = [];
    while ($row = $result->fetch_assoc()) {
        $menu[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $menu;
}

// Lấy thông tin 1 món ăn
function getDishById($id) {
    $conn = getConnection();
    $sql = "SELECT * FROM dishes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $res;
}

// =========================================================
// 2. CÁC HÀM TÁC ĐỘNG DỮ LIỆU (WRITE)
// =========================================================

// Lưu kết quả quay
function saveDishSelection($dishId, $dayOfWeek, $category, $userId) {
    if (!$userId) return false;

    $conn = getConnection();
    $weekNumber = getWeekNumber();
    $year = getYear();
    
    $sql = "INSERT INTO menu_history (user_id, dish_id, category, day_of_week, week_number, year) 
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE dish_id = VALUES(dish_id), selected_at = CURRENT_TIMESTAMP";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissii", $userId, $dishId, $category, $dayOfWeek, $weekNumber, $year);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    return $success;
}

// ✅ CẬP NHẬT MÓN ĂN (CHỈ CỦA MÌNH)
function updateDish($id, $name, $description, $category, $userId) {
    $conn = getConnection();
    $name = trim($name);
    
    // 1. Check quyền sở hữu
    $check = $conn->prepare("SELECT id FROM dishes WHERE id = ? AND user_id = ?");
    $check->bind_param("ii", $id, $userId);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $check->close();
        $conn->close();
        return ['success' => false, 'message' => 'Bạn không có quyền sửa món này!'];
    }
    $check->close();

    // 2. Update
    $sql = "UPDATE dishes SET name = ?, description = ?, category = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $name, $description, $category, $id, $userId);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Cập nhật thành công!'];
    } else {
        $err = $stmt->error;
        $stmt->close();
        $conn->close();
        error_log('Wheel updateDish failed: ' . $err);
        return ['success' => false, 'message' => 'Không thể cập nhật. Vui lòng thử lại.'];
    }
}

// ✅ XÓA MÓN ĂN (CHỈ CỦA MÌNH)
function deleteDish($id, $userId) {
    $conn = getConnection();
    
    // Chỉ xóa món của chính mình
    $sql = "DELETE FROM dishes WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $userId);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        $stmt->close();
        $conn->close();
        if ($affected > 0) {
            return ['success' => true, 'message' => 'Đã xóa món ăn!'];
        } else {
            return ['success' => false, 'message' => 'Không thể xóa (Không phải món của bạn)'];
        }
    } else {
        $err = $stmt->error;
        $stmt->close();
        $conn->close();
        error_log('Wheel deleteDish failed: ' . $err);
        return ['success' => false, 'message' => 'Lỗi khi xóa. Vui lòng thử lại.'];
    }
}

// =========================================================
// 3. CÁC HÀM TIỆN ÍCH
// =========================================================

function getCategoryName($category) {
    $names = ['man' => 'Món Mặn', 'rau' => 'Món Rau', 'canh' => 'Món Canh'];
    return $names[$category] ?? $category;
}

function hasDishForDay($dayOfWeek, $category, $userId) {
    if (!$userId) return false;
    $conn = getConnection();
    $weekNumber = getWeekNumber();
    $year = getYear();
    
    $sql = "SELECT COUNT(*) as count FROM menu_history 
            WHERE user_id = ? AND day_of_week = ? AND category = ? AND week_number = ? AND year = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issii", $userId, $dayOfWeek, $category, $weekNumber, $year);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $res['count'] > 0;
}
?>