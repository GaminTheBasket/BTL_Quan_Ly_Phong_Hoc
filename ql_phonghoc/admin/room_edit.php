<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Sửa Thông Tin Phòng";

// Lấy ID phòng từ URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID phòng không hợp lệ!";
    header("Location: rooms.php");
    exit();
}

$room_id = intval($_GET['id']); // ID của phòng đang sửa

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_name = trim($_POST['room_name']);
    $capacity = intval($_POST['capacity']);
    $equipment = trim($_POST['equipment']);
    $status = $_POST['status'];
    
    if (empty($room_name) || $capacity <= 0) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Kiểm tra trùng tên (trừ chính nó)
        $check_sql = "SELECT id FROM rooms WHERE room_name = ? AND id != ?";
        $existing = fetchOne($conn, $check_sql, "si", [$room_name, $room_id]);
        
        if ($existing) {
            $_SESSION['error'] = "Tên phòng đã tồn tại!";
        } else {
            $update_sql = "UPDATE rooms SET room_name = ?, capacity = ?, equipment = ?, status = ? WHERE id = ?";
            if (executeQuery($conn, $update_sql, "sissi", [$room_name, $capacity, $equipment, $status, $room_id])) {
                $_SESSION['success'] = "Cập nhật phòng thành công!";
                header("Location: rooms.php");
                exit();
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra!";
            }
        }
    }
}

// Lấy thông tin phòng
$room = fetchOne($conn, "SELECT * FROM rooms WHERE id = ?", "i", [$room_id]);

if (!$room) {
    $_SESSION['error'] = "Không tìm thấy phòng!";
    header("Location: rooms.php");
    exit();
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
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Sửa Thông Tin Phòng</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Phòng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="room_name" name="room_name" required
                               value="<?php echo htmlspecialchars($room['room_name']); ?>">
                        
                        <div id="room_name_error" class="text-danger small mt-1" style="display: none;">
                            <i class="fas fa-times-circle me-1"></i>Tên phòng này đã tồn tại.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sức Chứa <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="capacity" min="1" required
                               value="<?php echo $room['capacity']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Thiết Bị</label>
                        <textarea class="form-control" name="equipment" rows="3"
                        ><?php echo htmlspecialchars($room['equipment']); ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Trạng Thái <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" required>
                            <option value="available" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>Sẵn sàng</option>
                            <option value="maintenance" <?php echo $room['status'] == 'maintenance' ? 'selected' : ''; ?>>Bảo trì</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="rooms.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitButton">
                            <i class="fas fa-save me-1"></i>Cập Nhật
                        </button>
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
    formData.append('exclude_id', excludeId); // Gửi ID cần loại trừ

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
    const roomNameInput = document.getElementById('room_name');
    const errorDiv = document.getElementById('room_name_error');
    const submitButton = document.getElementById('submitButton');
    const currentRoomId = <?php echo $room_id; ?>; // Lấy ID từ PHP

    const debouncedCheck = debounce(checkDuplicate, 500);

    roomNameInput.addEventListener('input', function() {
        debouncedCheck('room_name', this.value.trim(), errorDiv, submitButton, currentRoomId);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>