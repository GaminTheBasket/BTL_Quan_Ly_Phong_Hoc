<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Nhập Người Dùng Hàng Loạt";
$success_count = 0;
$error_count = 0;
$error_messages = [];

// === BƯỚC 1: XỬ LÝ FILE KHI ĐƯỢC TẢI LÊN ===
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['user_file'])) {
    
    if ($_FILES['user_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Có lỗi khi tải file lên!";
    } else {
        $file_name = $_FILES['user_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['user_file']['name'], PATHINFO_EXTENSION));

        if ($file_ext != 'csv') {
            $_SESSION['error'] = "Chỉ chấp nhận file .csv (Excel)!";
        } else {
            if (($handle = fopen($file_name, "r")) !== FALSE) {
                
                // === NÂNG CẤP: SỬA CÂU SQL INSERT (thêm major_id) ===
                $insert_sql = "INSERT INTO users (username, password, full_name, role, ten_lop, major_id) VALUES (?, ?, ?, 'student', ?, ?)";
                $stmt = $conn->prepare($insert_sql);

                // Bỏ qua dòng tiêu đề (dòng đầu tiên)
                fgetcsv($handle, 1000, ","); 
                
                $line_number = 1; 

                // Đọc file từng dòng
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $line_number++;
                    
                    // === NÂNG CẤP: Yêu cầu 5 cột ===
                    if (count($data) >= 5) {
                        $username = trim($data[0]);
                        $password = trim($data[1]);
                        $full_name = trim($data[2]);
                        $ten_lop = trim($data[3]);
                        $ma_nganh = trim($data[4]); // Cột thứ 5 (Mã Ngành)

                        // Validate dữ liệu cơ bản
                        if (empty($username) || empty($password) || empty($full_name) || empty($ten_lop)) {
                            $error_count++;
                            $error_messages[] = "Dòng $line_number: Bị trống dữ liệu (4 cột đầu là bắt buộc).";
                            continue; 
                        }

                        // Kiểm tra xem username đã tồn tại chưa
                        $check = fetchOne($conn, "SELECT id FROM users WHERE username = ?", "s", [$username]);
                        if ($check) {
                            $error_count++;
                            $error_messages[] = "Dòng $line_number: Username '$username' đã tồn tại.";
                            continue; 
                        }
                        
                        // === NÂNG CẤP: TÌM major_id TỪ ma_nganh ===
                        $major_id = NULL; // Mặc định là NULL
                        if (!empty($ma_nganh)) {
                            $major = fetchOne($conn, "SELECT id FROM majors WHERE ma_nganh = ?", "s", [$ma_nganh]);
                            if ($major) {
                                $major_id = $major['id'];
                            } else {
                                $error_count++;
                                $error_messages[] = "Dòng $line_number: Không tìm thấy Mã Ngành '$ma_nganh' trong CSDL.";
                                continue; // Bỏ qua dòng này
                            }
                        }
                        // ======================================

                        // Mã hóa mật khẩu
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        // === NÂNG CẤP: SỬA BIND_PARAM (thêm 'i' cho major_id) ===
                        $stmt->bind_param("ssssi", $username, $hashed_password, $full_name, $ten_lop, $major_id);
                        if ($stmt->execute()) {
                            $success_count++;
                        } else {
                            $error_count++;
                            $error_messages[] = "Dòng $line_number: Lỗi CSDL khi chèn '$username'.";
                        }
                    } else {
                        $error_count++;
                        $error_messages[] = "Dòng $line_number: Không đủ 5 cột.";
                    }
                } // Kết thúc vòng lặp while
                
                fclose($handle);
                $stmt->close();

                // Đặt thông báo thành công
                $_SESSION['success'] = "Nhập file hoàn tất! <strong>$success_count</strong> sinh viên đã được thêm. <strong>$error_count</strong> dòng bị lỗi.";
                
                if ($error_count > 0) {
                    $_SESSION['import_errors'] = $error_messages;
                }

            } else {
                $_SESSION['error'] = "Không thể mở file CSV.";
            }
        }
    }
    
    header("Location: user_import.php"); // Tải lại trang để hiển thị thông báo
    exit();
}

// === BƯỚC 2: HIỂN THỊ GIAO DIỆN ===
require_once '../includes/header.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['import_errors'])): ?>
    <div class="alert alert-warning">
        <h5 class="alert-heading">Chi tiết các dòng bị lỗi:</h5>
        <ul>
            <?php 
            foreach ($_SESSION['import_errors'] as $msg) {
                echo "<li>" . htmlspecialchars($msg) . "</li>";
            }
            unset($_SESSION['import_errors']);
            ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Hướng Dẫn Sử Dụng</h5>
            </div>
            <div class="card-body">
                <p>Tính năng này cho phép bạn nhập hàng loạt sinh viên (vai trò 'student') từ một file <strong>.csv</strong>.</p>
                <hr>
                <h6><strong>Yêu cầu file .csv:</strong></h6>
                <ol>
                    <li>File phải có <strong>5 cột</strong> và có thứ tự chính xác.</li>
                    <li>Dòng đầu tiên (tiêu đề) sẽ bị <strong>bỏ qua</strong>.</li>
                    <li>Định dạng các cột:</li>
                </ol>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Cột A</th>
                            <th>Cột B</th>
                            <th>Cột C</th>
                            <th>Cột D</th>
                            <th>Cột E</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>username</td>
                            <td>password</td>
                            <td>full_name</td>
                            <td>ten_lop</td>
                            <td>ma_nganh</td>
                        </tr>
                        <tr>
                            <td>sv002</td>
                            <td>123456</td>
                            <td>Trần Văn B</td>
                            <td>IT-K18</td>
                            <td>CNTT</td>
                        </tr>
                        <tr>
                            <td>sv003</td>
                            <td>123456</td>
                            <td>Lê Thị C</td>
                            <td>Ketoan-K19</td>
                            <td>KT</td>
                        </tr>
                        <tr>
                            <td>sv004</td>
                            <td>123456</td>
                            <td>Phạm Văn D</td>
                            <td>IT-K18</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <p class="text-muted small">Cột `ma_nganh` (Mã Ngành) phải khớp với Mã Ngành trong mục "Quản lý Ngành học". Có thể để trống.</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-7 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-upload me-2"></i>Tải lên file .csv</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="user_file" class="form-label fw-bold">Chọn file (chỉ .csv)</label>
                        <input class="form-control" type="file" id="user_file" name="user_file" 
                               accept=".csv" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-check me-1"></i> Bắt đầu Nhập liệu
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>