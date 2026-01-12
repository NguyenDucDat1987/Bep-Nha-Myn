-- Bảng danh sách món ăn (CÓ THÊM CỘT LOẠI MÓN)
CREATE TABLE dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category ENUM('man', 'rau', 'canh') NOT NULL COMMENT 'Loại món: mặn, rau, canh',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng lịch sử quay (CÓ THÊM CỘT LOẠI MÓN)
CREATE TABLE menu_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dish_id INT NOT NULL,
    category VARCHAR(20) NOT NULL COMMENT 'Loại món được chọn',
    day_of_week VARCHAR(20) NOT NULL,
    week_number INT NOT NULL,
    year INT NOT NULL,
    selected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_day_category (day_of_week, category, week_number, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert dữ liệu mẫu: MÓN MẶN
INSERT INTO dishes (name, category, description) VALUES
('Thịt kho tàu', 'man', '- 500g thịt ba chỉ\n- 2 thìa nước mắm\n- 1 thìa đường\n- 3 trứng'),
('Cá kho', 'man', '- 2 con cá\n- Nước mắm, đường, ớt'),
('Gà xào sả ớt', 'man', '- 500g gà\n- Sả, ớt, tỏi'),
('Sườn rim', 'man', '- 500g sườn\n- Nước mắm, đường'),
('Tôm rang thịt', 'man', '- 200g tôm\n- 100g thịt băm'),
('Trứng chiên', 'man', '- 3 quả trứng\n- Hành phi');

-- Insert dữ liệu mẫu: MÓN RAU
INSERT INTO dishes (name, category, description) VALUES
('Rau muống xào tỏi', 'rau', '- 1 bó rau muống\n- 3 tép tỏi\n- Nước mắm'),
('Cải xào', 'rau', '- 1 bó cải\n- Tỏi, dầu ăn'),
('Rau luộc', 'rau', '- Các loại rau theo mùa\n- Luộc chín'),
('Đậu xào', 'rau', '- 200g đậu\n- Tỏi, dầu'),
('Su hào xào', 'rau', '- 1 củ su hào\n- Thịt băm, nước mắm'),
('Bí xào', 'rau', '- 300g bí\n- Tỏi, dầu ăn');

-- Insert dữ liệu mẫu: MÓN CANH
INSERT INTO dishes (name, category, description) VALUES
('Canh chua cá', 'canh', '- Cá lóc\n- Rau muống, cà chua\n- Me, đường'),
('Canh rau ngót', 'canh', '- 1 bó rau ngót\n- Tôm khô hoặc thịt băm'),
('Canh khổ qua', 'canh', '- 1 trái khổ qua\n- Thịt băm, trứng'),
('Canh cải thịt', 'canh', '- Cải xanh\n- Thịt băm'),
('Canh mướp', 'canh', '- 2 trái mướp\n- Tôm khô'),
('Canh bí đỏ', 'canh', '- 300g bí đỏ\n- Tôm khô');