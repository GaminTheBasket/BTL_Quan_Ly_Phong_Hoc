<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    echo json_encode([]);
    exit();
}

$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

if ($room_id > 0) {
    $bookings = fetchAll($conn, "
        SELECT id, start_time, end_time, purpose, status
        FROM bookings
        WHERE room_id = ? AND status != 'rejected'
        ORDER BY start_time
    ", "i", [$room_id]);
    
    echo json_encode($bookings);
} else {
    echo json_encode([]);
}
?>