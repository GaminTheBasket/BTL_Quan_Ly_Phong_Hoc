<?php
// File kết nối cơ sở dữ liệu
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'nyminh282005'); // <- ĐÃ SỬA DÒNG NÀY
define('DB_NAME', 'ql_phonghoc');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function executeQuery($conn, $sql, $types = "", $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $result = $stmt->execute();
    if (strtoupper(substr(trim($sql), 0, 6)) === 'SELECT') {
        return $stmt->get_result();
    }
    return $result;
}

function fetchOne($conn, $sql, $types = "", $params = []) {
    $result = executeQuery($conn, $sql, $types, $params);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function fetchAll($conn, $sql, $types = "", $params = []) {
    $result = executeQuery($conn, $sql, $types, $params);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}
?>