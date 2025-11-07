<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Sửa Người Dùng";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID không hợp lệ!";
    header("Location: users.php");
    exit();
}

$user_id = intval($_GET['id']); // ID của user đang sửa

// Lấy danh sách Ngành học
$majors = fetchAll($conn, "SELECT * FROM majors ORDER BY ten_nganh");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $new_password = trim($_POST['new_password']);
    
    $major_id = (!empty($_POST['major_id'])) ? intval($_POST['major_id']) : NULL;
    $ten_lop = ($role == 'student') ? trim($_POST['ten_lop']) : NULL;
    
    if (empty($username) || empty($full_name)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        $check = fetchOne($conn, "SELECT id FROM users WHERE username = ? AND id != ?", "si", [$username, $user_id]);
        
        if ($check) {
            $_SESSION['error'] = "Tên đăng nhập đã tồn tại!";
        } else {
            
            if (!empty($new_password)) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET username=?, password=?, full_name=?, role=?, ten_lop=?, major_id=? WHERE id=?";
                $success = executeQuery($conn, $update_sql, "sssssii", [$username, $hashed, $full_name, $role, $ten_lop, $major_id, $user_id]);
            } else {
                $update_sql = "UPDATE users SET username=?, full_name=?, role=?, ten_lop=?, major_id=? WHERE id=?";
                $success = executeQuery($conn, $update_sql, "ssssii", [$username, $full_name, $role, $ten_lop, $major_id, $user_id]);
            }
            
            if ($success) {
                $_SESSION['success'] = "Cập nhật thành công!";
                header("Location: users.php");
                exit();
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra!";
            }
        }
    }
}

// Lấy thông tin người dùng hiện tại
$user = fetchOne($conn, "SELECT * FROM users WHERE id = ?", "i", [$user_id]);
if (!$user) {
    $_SESSION['error'] = "Không tìm thấy người dùng!";
    header("Location: users.php");
    exit();
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Sửa Thông Tin Người Dùng</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required
                               value="<?php echo htmlspecialchars($user['username']); ?>">
                        
                        <div id="username_error" class="text-danger small mt-1" style="display: none;">
                            <i class="fas fa-times-circle me-1"></i>Tên đăng nhập này đã tồn tại.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Họ và tên</label>
                        <input type="text" class="form-control" name="full_name" required
                               value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Vai trò</label>
                        <select class="form-select" name="role" id="roleSelect" required>
                            <option value="teacher" <?php echo $user['role'] == 'teacher' ? 'selected' : ''; ?>>Giảng viên</option>
                            <option value="student" <?php echo $user['role'] == 'student' ? 'selected' : ''; ?>>Sinh viên</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Ngành học (Khoa)</label>
                        <select class="form-select" name="major_id">
                            <option value="">-- Không thuộc ngành nào --</option>
                            <?php foreach ($majors as $major): ?>
                                <option value="<?php echo $major['id']; ?>" <?php echo ($user['major_id'] == $major['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($major['ten_nganh']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4" id="tenLopWrapper" style="display: <?php echo ($user['role'] == 'student') ? 'block' : 'none'; ?>;">
                        <label class="form-label fw-bold">Tên Lớp (cho Sinh viên)</label>
                        <input type="text" class="form-control" name="ten_lop" 
                               placeholder="VD: IT-K18"
                               value="<?php echo htmlspecialchars($user['ten_lop']); ?>">
                        <small class="text-muted">Tên lớp phải giống nhau để sinh viên thấy chung lịch.</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Mật khẩu mới (để trống nếu không đổi)</label>
                        <input type="password" class="form-control" name="new_password" 
                               placeholder="Nhập mật khẩu mới nếu muốn đổi">
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="users.php" class="btn btn-secondary">Quay lại</a>
                        <button type="submit" class="btn btn-primary" id="submitButton">Cập Nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Hàm debounce (trì hoãn)
function debounce(func, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

// Hàm kiểm tra chung
async function checkDuplicate(type, value, errorDiv, submitButton, excludeId) {
    if (value === '') {
        errorDiv.style.display = 'none';
        submitButton.disabled = false;
        return;
    }
    const formData = new FormData();
    formData.append('type', type);
    formData.append('value', value);
    formData.append('exclude_id', excludeId);
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
    document.getElementById('roleSelect').addEventListener('change', function() {
        const tenLopWrapper = document.getElementById('tenLopWrapper');
        if (this.value === 'student') {
            tenLopWrapper.style.display = 'block';
        } else {
            tenLopWrapper.style.display = 'none';
        }
    });
    
    // 2. Script AJAX cho Username
    const usernameInput = document.getElementById('username');
    const errorDiv = document.getElementById('username_error');
    const submitButton = document.getElementById('submitButton');
    const currentUserId = <?php echo $user_id; ?>; // Lấy ID từ PHP

    const debouncedCheck = debounce(checkDuplicate, 500);

    usernameInput.addEventListener('input', function() {
        debouncedCheck('username', this.value.trim(), errorDiv, submitButton, currentUserId);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>