<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo json_encode([]); 
    exit();
}

// 1. Lấy user_id của sinh viên
$user_id = $_SESSION['user_id'];

// 2. Tìm 'ten_lop' của sinh viên
$student = fetchOne($conn, "SELECT ten_lop FROM users WHERE id = ?", "i", [$user_id]);

if (!$student || empty($student['ten_lop'])) {
    echo json_encode([]); // Sinh viên chưa có lớp
    exit();
}

$ten_lop = $student['ten_lop'];

// 3. === SỬA LẠI CÂU SQL ===
// Lấy lịch học dựa trên 'ten_lop'
$bookings = fetchAll($conn, "
    SELECT 
        b.id, 
        b.start_time, 
        b.end_time, 
        
        -- Nâng cấp logic:
        -- Nếu subject_id có (liên kết với bảng subjects), lấy 'ten_mon_hoc'
        -- Nếu subject_id là NULL (là 'Mục đích khác'), lấy 'purpose'
        COALESCE(s.ten_mon_hoc, b.purpose) as purpose, 
        
        r.room_name,
        u.full_name as teacher_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN users u ON b.user_id = u.id
    
    -- Nối bảng subjects (Môn học) một cách tùy chọn (LEFT JOIN)
    LEFT JOIN subjects s ON b.subject_id = s.id 
    
    WHERE 
        b.ten_lop = ?
        AND b.status = 'approved'
    ORDER BY b.start_time
", "s", [$ten_lop]);

// 4. Trả về kết quả
echo json_encode($bookings);
exit();
?>