<?php
session_start();
require_once '../includes/db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Thêm Phòng Mới";

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_name = trim($_POST['room_name']);
    $capacity = intval($_POST['capacity']);
    $equipment = trim($_POST['equipment']);
    $status = $_POST['status'];
    
    // Validate
    if (empty($room_name) || $capacity <= 0) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Kiểm tra trùng tên phòng
        $check_sql = "SELECT id FROM rooms WHERE room_name = ?";
        $existing = fetchOne($conn, $check_sql, "s", [$room_name]);
        
        if ($existing) {
            $_SESSION['error'] = "Tên phòng đã tồn tại!";
        } else {
            // Thêm phòng mới
            $insert_sql = "INSERT INTO rooms (room_name, capacity, equipment, status) VALUES (?, ?, ?, ?)";
            if (executeQuery($conn, $insert_sql, "siss", [$room_name, $capacity, $equipment, $status])) {
                $_SESSION['success'] = "Thêm phòng thành công!";
                header("Location: rooms.php");
                exit();
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi thêm phòng!";
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
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Thêm Phòng Học Mới</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="room_name" class="form-label fw-bold">
                            Tên Phòng <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="room_name" name="room_name" 
                               placeholder="VD: A1-201" required 
                               value="<?php echo isset($_POST['room_name']) ? htmlspecialchars($_POST['room_name']) : ''; ?>">
                        
                        <div id="room_name_error" class="text-danger small mt-1" style="display: none;">
                            <i class="fas fa-times-circle me-1"></i>Tên phòng này đã tồn tại.
                        </div>
                        
                        <small class="text-muted">Tên phòng phải là duy nhất</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="capacity" class="form-label fw-bold">
                            Sức Chứa <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="capacity" name="capacity" 
                               placeholder="VD: 50" min="1" required
                               value="<?php echo isset($_POST['capacity']) ? $_POST['capacity'] : ''; ?>">
                        <small class="text-muted">Số lượng người tối đa</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="equipment" class="form-label fw-bold">Thiết Bị</label>
                        <textarea class="form-control" id="equipment" name="equipment" 
                                  rows="3" placeholder="VD: Máy chiếu, Máy tính, Bảng thông minh"
                        ><?php echo isset($_POST['equipment']) ? htmlspecialchars($_POST['equipment']) : ''; ?></textarea>
                        <small class="text-muted">Mô tả các thiết bị có trong phòng</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="status" class="form-label fw-bold">
                            Trạng Thái <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="available" <?php echo (isset($_POST['status']) && $_POST['status'] == 'available') ? 'selected' : ''; ?>>
                                Sẵn sàng
                            </option>
                            <option value="maintenance" <?php echo (isset($_POST['status']) && $_POST['status'] == 'maintenance') ? 'selected' : ''; ?>>
                                Bảo trì
                            </option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="rooms.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary btn-custom" id="submitButton">
                            <i class="fas fa-save me-1"></i>Lưu Phòng
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
    // Không kiểm tra rỗng
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
            errorDiv.style.display = 'block'; // Hiển thị lỗi
            submitButton.disabled = true; // Vô hiệu hóa nút Lưu
        } else {
            errorDiv.style.display = 'none'; // Ẩn lỗi
            submitButton.disabled = false; // Kích hoạt nút Lưu
        }
    } catch (error) {
        console.error('Lỗi kiểm tra AJAX:', error);
        submitButton.disabled = false; // Kích hoạt nếu có lỗi
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const roomNameInput = document.getElementById('room_name');
    const errorDiv = document.getElementById('room_name_error');
    const submitButton = document.getElementById('submitButton');

    // Tạo phiên bản trì hoãn của hàm checkDuplicate
    const debouncedCheck = debounce(checkDuplicate, 500); // Trì hoãn 500ms

    // Gọi khi người dùng gõ xong
    roomNameInput.addEventListener('input', function() {
        debouncedCheck('room_name', this.value.trim(), errorDiv, submitButton);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>