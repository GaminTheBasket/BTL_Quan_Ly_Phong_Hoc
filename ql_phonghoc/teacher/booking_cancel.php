<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID không hợp lệ!";
    header("Location: index.php");
    exit();
}

$booking_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$booking = fetchOne($conn, "SELECT * FROM bookings WHERE id = ? AND user_id = ?", "ii", [$booking_id, $user_id]);

if (!$booking) {
    $_SESSION['error'] = "Không tìm thấy yêu cầu đặt phòng!";
    header("Location: index.php");
    exit();
}

if ($booking['status'] == 'rejected') {
    $_SESSION['error'] = "Yêu cầu đã bị từ chối, không thể hủy!";
} elseif ($booking['status'] == 'approved' && strtotime($booking['start_time']) <= time()) {
    $_SESSION['error'] = "Không thể hủy lịch đã qua hoặc đang diễn ra!";
} else {
    $delete_sql = "DELETE FROM bookings WHERE id = ?";
    if (executeQuery($conn, $delete_sql, "i", [$booking_id])) {
        $_SESSION['success'] = "Hủy đặt phòng thành công!";
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra khi hủy!";
    }
}

header("Location: index.php");
exit();
?>