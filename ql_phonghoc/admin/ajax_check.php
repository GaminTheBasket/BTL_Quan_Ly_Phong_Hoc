<?php
session_start();
require_once '../includes/db_connect.php';

$response = ['exists' => false];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$type = $_POST['type'] ?? '';
$value = trim($_POST['value'] ?? '');
$exclude_id = isset($_POST['exclude_id']) ? intval($_POST['exclude_id']) : 0;

if (empty($value)) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$sql = "";
$params = [$value]; // <-- $params luôn là một mảng
$types = "s";

switch ($type) {
    case 'room_name':
        $sql = "SELECT id FROM rooms WHERE room_name = ?";
        break;
    case 'username':
        $sql = "SELECT id FROM users WHERE username = ?";
        break;
    case 'ma_nganh':
        $sql = "SELECT id FROM majors WHERE ma_nganh = ?";
        break;
    case 'ten_nganh':
        $sql = "SELECT id FROM majors WHERE ten_nganh = ?";
        break;
    case 'ma_mon_hoc':
        $sql = "SELECT id FROM subjects WHERE ma_mon_hoc = ?";
        break;
    case 'ten_mon_hoc':
        $sql = "SELECT id FROM subjects WHERE ten_mon_hoc = ?";
        break;
}

if ($sql && $exclude_id > 0) {
    $sql .= " AND id != ?";
    $params[] = $exclude_id; // Thêm vào mảng $params
    $types .= "i";
}

if ($sql) {
    // === SỬA LỖI TẠI DÒNG DƯỚI ===
    // Xóa dấu ... (ba chấm) khỏi ...$params
    $check = fetchOne($conn, $sql, $types, $params); // ĐÃ XÓA ...
    if ($check) {
        $response['exists'] = true;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>