<?php
session_start();
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
        header("Location: index.php");
        exit();
    }
    
    $sql = "SELECT id, username, password, full_name, role FROM users WHERE username = ?";
    $user = fetchOne($conn, $sql, "s", [$username]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        // SỬA Ở ĐÂY
        switch ($user['role']) {
            case 'admin':
                header("Location: admin/index.php"); // Admin giữ nguyên
                break;
            case 'teacher':
                header("Location: home.php"); // <-- Đổi thành home.php
                break;
            case 'student':
                header("Location: home.php"); // <-- Đổi thành home.php
                break;
            default:
                $_SESSION['error'] = "Vai trò không hợp lệ!";
                header("Location: index.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Tên đăng nhập hoặc mật khẩu không đúng!";
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>