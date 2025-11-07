<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    echo json_encode([]); 
    exit();
}

// Lấy TẤT CẢ các lịch đã được duyệt (approved)
$bookings = fetchAll($conn, "
    SELECT 
        b.id, 
        b.start_time, 
        b.end_time, 
        COALESCE(s.ten_mon_hoc, b.purpose) as purpose, 
        r.room_name,
        u.full_name as teacher_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN subjects s ON b.subject_id = s.id 
    WHERE 
        b.status = 'approved'
    ORDER BY b.start_time
");

// Trả về kết quả
echo json_encode($bookings);
exit();
?>