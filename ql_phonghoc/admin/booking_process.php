<?php
session_start();
require_once '../includes/db_connect.php';

// Mặc định là lỗi
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

// 1. Kiểm tra Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    $response['message'] = 'Không có quyền truy cập.';
    echo json_encode($response);
    exit();
}

// 2. Kiểm tra xem dữ liệu có được gửi qua POST không
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Đọc dữ liệu JSON gửi từ JavaScript
    $data = json_decode(file_get_contents('php://input'), true);
    
    $booking_id = isset($data['id']) ? intval($data['id']) : 0;
    $action = isset($data['action']) ? $data['action'] : '';

    if ($booking_id > 0 && ($action == 'approve' || $action == 'reject')) {
        
        $new_status = ($action == 'approve') ? 'approved' : 'rejected';
        
        $update_sql = "UPDATE bookings SET status = ? WHERE id = ? AND status = 'pending'";
        
        // Sử dụng hàm executeQuery của bạn
        if (executeQuery($conn, $update_sql, "si", [$new_status, $booking_id])) {
            $response['success'] = true;
            $response['message'] = ($new_status == 'approved') ? 'Đã duyệt thành công!' : 'Đã từ chối!';
            $response['new_status'] = $new_status;
        } else {
            $response['message'] = 'Lỗi CSDL hoặc yêu cầu đã được xử lý.';
        }
    } else {
        $response['message'] = 'Dữ liệu không hợp lệ.';
    }
}

// 3. Trả về kết quả dưới dạng JSON
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>