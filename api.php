<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';
require_once 'auth_functions.php';

// Tắt báo lỗi PHP ra màn hình để tránh hỏng JSON
error_reporting(0);
ini_set('display_errors', 0);

$conn = getConnection();
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// ✅ CHO PHÉP GUEST XEM GỢI Ý MÓN (không cần đăng nhập)
if ($action === 'get_suggestions') {
    handleGetSuggestions($conn, $input);
    $conn->close();
    exit;
}

// CHẶN: Các API khác chỉ dành cho User đã đăng nhập
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Phiên đăng nhập hết hạn!']);
    $conn->close();
    exit;
}

$userId = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'add_dish':
            // Thêm món và GẮN TÊN CHỦ SỞ HỮU (user_id)
            $name = trim($input['name'] ?? '');
            $cat = $input['category'] ?? '';
            $desc = trim($input['description'] ?? '');
            
            if (!$name || !$cat) throw new Exception("Thiếu tên hoặc loại món");

            // Check trùng trong danh sách của User này (User khác có tên trùng kệ họ)
            $chk = $conn->prepare("SELECT id FROM dishes WHERE name = ? AND category = ? AND user_id = ?");
            $chk->bind_param("ssi", $name, $cat, $userId);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) throw new Exception("Bạn đã có món này rồi!");
            $chk->close();

            // Insert
            $stmt = $conn->prepare("INSERT INTO dishes (name, category, description, user_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $cat, $desc, $userId);
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Đã thêm món vào bếp của bạn!',
                    'data' => ['id' => $stmt->insert_id, 'name' => $name, 'category' => $cat, 'description' => $desc]
                ]);
            } else {
                throw new Exception($stmt->error);
            }
            $stmt->close();
            break;

        case 'delete_dish':
            // 🔥 FIX CHÍNH: Xóa món từ thực đơn tuần (menu_history) hoặc danh sách gốc (dishes)
            $id = intval($input['id']);
            
            // BƯỚC 1: Thử xóa từ menu_history TRƯỚC (vì nút xóa trên trang chủ là xóa thực đơn tuần)
            $stmt = $conn->prepare("DELETE FROM menu_history WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                // ✅ Xóa thành công từ thực đơn tuần
                $stmt->close();
                echo json_encode(['status' => 'success', 'message' => 'Đã xóa khỏi thực đơn tuần!']);
            } else {
                // BƯỚC 2: Không phải menu_history → Thử xóa từ dishes (danh sách gốc)
                $stmt->close();
                $stmt2 = $conn->prepare("DELETE FROM dishes WHERE id = ? AND user_id = ?");
                $stmt2->bind_param("ii", $id, $userId);
                $stmt2->execute();
                
                if ($stmt2->affected_rows > 0) {
                    // ✅ Xóa thành công từ danh sách gốc
                    $stmt2->close();
                    echo json_encode(['status' => 'success', 'message' => 'Đã xóa món khỏi bếp của bạn!']);
                } else {
                    // ❌ Không xóa được (Có thể không phải của user này)
                    $stmt2->close();
                    throw new Exception("Không thể xóa món này!");
                }
            }
            break;

        case 'reset_week':
            $week = date('W'); 
            $year = date('Y');
            $stmt = $conn->prepare("DELETE FROM menu_history WHERE user_id = ? AND week_number = ? AND year = ?");
            $stmt->bind_param("iii", $userId, $week, $year);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['status' => 'success', 'message' => 'Đã reset tuần của bạn!']);
            break;

        default:
            throw new Exception("Action không hợp lệ");
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
$conn->close();

// 👇 HÀM XỬ LÝ GỢI Ý - CHO PHÉP GUEST SỬ DỤNG 👇
function handleGetSuggestions($conn, $data) {
    $keyword = trim($data['keyword'] ?? '');
    // Chỉ tìm khi gõ trên 2 ký tự
    if (strlen($keyword) < 2) {
        echo json_encode(['status' => 'success', 'data' => []]);
        return;
    }

    // Tìm các món có tên gần giống (trong toàn bộ hệ thống)
    // Dùng DISTINCT để tránh trùng lặp tên
    $sql = "SELECT DISTINCT name FROM dishes WHERE name LIKE ? ORDER BY name LIMIT 10";
    $stmt = $conn->prepare($sql);
    $param = "%$keyword%";
    $stmt->bind_param("s", $param);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $suggestions = [];
    while($row = $result->fetch_assoc()) {
        $suggestions[] = $row['name'];
    }
    echo json_encode(['status' => 'success', 'data' => $suggestions]);
    $stmt->close();
}
?>