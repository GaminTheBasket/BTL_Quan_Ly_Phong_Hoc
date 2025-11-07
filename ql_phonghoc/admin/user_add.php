<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Thêm Người Dùng";

// Lấy danh sách Ngành học
$majors = fetchAll($conn, "SELECT * FROM majors ORDER BY ten_nganh");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    
    // SỬA LOGIC:
    $major_id = (!empty($_POST['major_id'])) ? intval($_POST['major_id']) : NULL;
    $ten_lop = ($role == 'student') ? trim($_POST['ten_lop']) : NULL; // Tên lớp VẪN CHỈ dành cho sinh viên
    
    if (empty($username) || empty($password) || empty($full_name)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = "Mật khẩu phải có ít nhất 6 ký tự!";
    } else {
        $check = fetchOne($conn, "SELECT id FROM users WHERE username = ?", "s", [$username]);
        
        if ($check) {
            $_SESSION['error'] = "Tên đăng nhập đã tồn tại!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Câu SQL đã đúng từ trước (chứa cả ten_lop và major_id)
            $insert_sql = "INSERT INTO users (username, password, full_name, role, ten_lop, major_id) VALUES (?, ?, ?, ?, ?, ?)";
            
            if (executeQuery($conn, $insert_sql, "sssssi", [$username, $hashed_password, $full_name, $role, $ten_lop, $major_id])) {
                $_SESSION['success'] = "Thêm người dùng thành công!";
                header("Location: users.php");
                exit();
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra!";
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Thêm Người Dùng Mới</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        
                        <div id="username_error" class="text-danger small mt-1" style="display: none;">
                            <i class="fas fa-times-circle me-1"></i>Tên đăng nhập này đã tồn tại.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                        <small class="text-muted">Phải có ít nhất 6 ký tự.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required 
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Vai trò <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" id="roleSelect" required>
                            <option value="">-- Chọn vai trò --</option>
                            <option value="teacher">Giảng viên</option>
                            <option value="student">Sinh viên</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Ngành học (Khoa)</label>
                        <select class="form-select" name="major_id">
                            <option value="">-- Không thuộc ngành nào --</option>
                            <?php foreach ($majors as $major): ?>
                                <option value="<?php echo $major['id']; ?>">
                                    <?php echo htmlspecialchars($major['ten_nganh']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4" id="tenLopWrapper" style="display: none;">
                        <label class="form-label fw-bold">Tên Lớp (cho Sinh viên)</label>
                        <input type="text" class="form-control" name="ten_lop" placeholder="VD: IT-K18">
                        <small class="text-muted">Tên lớp phải giống nhau để sinh viên thấy chung lịch.</small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="users.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitButton">
                            <i class="fas fa-save me-1"></i>Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Hàm debounce (trì hoãn) để không gọi AJAX liên tục
function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// Hàm kiểm tra chung
async function checkDuplicate(type, value, errorDiv, submitButton) {
    if (value === '') {
        errorDiv.style.display = 'none';
        submitButton.disabled = false;
        return;
    }
    const formData = new FormData();
    formData.append('type', type);
    formData.append('value', value);
    try {
        const response = await fetch('ajax_check.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.exists) {
            errorDiv.style.display = 'block';
            submitButton.disabled = true;
        } else {
            errorDiv.style.display = 'none';
            submitButton.disabled = false;
        }
    } catch (error) {
        console.error('Lỗi kiểm tra AJAX:', error);
        submitButton.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Script cho Role Select (giữ nguyên)
    const roleSelect = document.getElementById('roleSelect');
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            const tenLopWrapper = document.getElementById('tenLopWrapper');
            if (this.value === 'student') {
                tenLopWrapper.style.display = 'block';
            } else {
                tenLopWrapper.style.display = 'none';
            }
        });
    }

    // 2. Script AJAX cho Username
    const usernameInput = document.getElementById('username');
    const errorDiv = document.getElementById('username_error');
    const submitButton = document.getElementById('submitButton');
    
    const debouncedCheck = debounce(checkDuplicate, 500);

    usernameInput.addEventListener('input', function() {
        debouncedCheck('username', this.value.trim(), errorDiv, submitButton);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>