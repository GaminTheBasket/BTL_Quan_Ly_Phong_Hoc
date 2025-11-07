<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Thêm Ngành Học";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ma_nganh = trim($_POST['ma_nganh']);
    $ten_nganh = trim($_POST['ten_nganh']);
    
    if (empty($ten_nganh)) {
        $_SESSION['error'] = "Tên ngành học là bắt buộc!";
    } else {
        // Kiểm tra xem mã hoặc tên đã tồn tại chưa
        $check = fetchOne($conn, "SELECT id FROM majors WHERE ma_nganh = ? OR ten_nganh = ?", "ss", [$ma_nganh, $ten_nganh]);
        if ($check) {
            $_SESSION['error'] = "Mã ngành hoặc Tên ngành đã tồn tại!";
        } else {
            $sql = "INSERT INTO majors (ma_nganh, ten_nganh) VALUES (?, ?)";
            if (executeQuery($conn, $sql, "ss", [$ma_nganh, $ten_nganh])) {
                $_SESSION['success'] = "Thêm ngành học thành công!";
                header("Location: majors.php");
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
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Thêm Ngành Học Mới</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Ngành</label>
                        <input type="text" class="form-control" id="ma_nganh" name="ma_nganh" 
                               placeholder="VD: CNTT"
                               value="<?php echo isset($_POST['ma_nganh']) ? htmlspecialchars($_POST['ma_nganh']) : ''; ?>">
                        
                        <div id="ma_nganh_error" class="text-danger small mt-1" style="display: none;">
                            <i class="fas fa-times-circle me-1"></i>Mã ngành này đã tồn tại.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Ngành Học <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_nganh" name="ten_nganh" 
                               placeholder="VD: Công nghệ thông tin" required
                               value="<?php echo isset($_POST['ten_nganh']) ? htmlspecialchars($_POST['ten_nganh']) : ''; ?>">
                        
                        <div id="ten_nganh_error" class="text-danger small mt-1" style="display: none;">
                            <i class="fas fa-times-circle me-1"></i>Tên ngành này đã tồn tại.
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="majors.php" class="btn btn-secondary">
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
// Hàm debounce (trì hoãn)
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
    const maNganhInput = document.getElementById('ma_nganh');
    const tenNganhInput = document.getElementById('ten_nganh');
    const maNganhError = document.getElementById('ma_nganh_error');
    const tenNganhError = document.getElementById('ten_nganh_error');
    const submitButton = document.getElementById('submitButton');

    const debouncedCheckMaNganh = debounce(checkDuplicate, 500);
    const debouncedCheckTenNganh = debounce(checkDuplicate, 500);

    maNganhInput.addEventListener('input', function() {
        debouncedCheckMaNganh('ma_nganh', this.value.trim(), maNganhError, submitButton);
    });
    
    tenNganhInput.addEventListener('input', function() {
        debouncedCheckTenNganh('ten_nganh', this.value.trim(), tenNganhError, submitButton);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>