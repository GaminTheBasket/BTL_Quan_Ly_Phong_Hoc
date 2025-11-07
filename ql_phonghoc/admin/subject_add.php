<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Thêm Môn Học";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ma_mon_hoc = trim($_POST['ma_mon_hoc']);
    $ten_mon_hoc = trim($_POST['ten_mon_hoc']);
    
    if (empty($ten_mon_hoc)) {
        $_SESSION['error'] = "Tên môn học là bắt buộc!";
    } else {
        // Kiểm tra xem mã hoặc tên đã tồn tại chưa
        $check = fetchOne($conn, "SELECT id FROM subjects WHERE ma_mon_hoc = ? OR ten_mon_hoc = ?", "ss", [$ma_mon_hoc, $ten_mon_hoc]);
        if ($check) {
            $_SESSION['error'] = "Mã môn học hoặc Tên môn học đã tồn tại!";
        } else {
            $sql = "INSERT INTO subjects (ma_mon_hoc, ten_mon_hoc) VALUES (?, ?)";
            if (executeQuery($conn, $sql, "ss", [$ma_mon_hoc, $ten_mon_hoc])) {
                $_SESSION['success'] = "Thêm môn học thành công!";
                header("Location: subjects.php");
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
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Thêm Môn Học Mới</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Môn Học</label>
                        <input type="text" class="form-control" id="ma_mon_hoc" name="ma_mon_hoc" 
                               placeholder="VD: IT101"
                               value="<?php echo isset($_POST['ma_mon_hoc']) ? htmlspecialchars($_POST['ma_mon_hoc']) : ''; ?>">
                        
                        <div id="ma_mon_hoc_error" class="text-danger small mt-1" style="display: none;">
                            <i class="fas fa-times-circle me-1"></i>Mã môn học này đã tồn tại.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên Môn Học <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_mon_hoc" name="ten_mon_hoc" 
                               placeholder="VD: Lập trình Web" required
                               value="<?php echo isset($_POST['ten_mon_hoc']) ? htmlspecialchars($_POST['ten_mon_hoc']) : ''; ?>">
                        
                        <div id="ten_mon_hoc_error" class="text-danger small mt-1" style="display: none;">
                            <i class="fas fa-times-circle me-1"></i>Tên môn học này đã tồn tại.
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="subjects.php" class="btn btn-secondary">
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
    const maMonHocInput = document.getElementById('ma_mon_hoc');
    const tenMonHocInput = document.getElementById('ten_mon_hoc');
    const maMonHocError = document.getElementById('ma_mon_hoc_error');
    const tenMonHocError = document.getElementById('ten_mon_hoc_error');
    const submitButton = document.getElementById('submitButton');

    const debouncedCheckMa = debounce(checkDuplicate, 500);
    const debouncedCheckTen = debounce(checkDuplicate, 500);

    maMonHocInput.addEventListener('input', function() {
        debouncedCheckMa('ma_mon_hoc', this.value.trim(), maMonHocError, submitButton);
    });
    
    tenMonHocInput.addEventListener('input', function() {
        debouncedCheckTen('ten_mon_hoc', this.value.trim(), tenMonHocError, submitButton);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>