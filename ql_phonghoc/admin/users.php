<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Quản lý Người dùng";

// === LẤY DỮ LIỆU LỌC ===
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';
$filter_lop = isset($_GET['lop']) ? $_GET['lop'] : '';
$filter_major = isset($_GET['major_id']) ? intval($_GET['major_id']) : 0;

// (Lấy danh sách Lớp và Ngành cho dropdown - giữ nguyên)
$lop_list = fetchAll($conn, "SELECT DISTINCT ten_lop FROM users WHERE ten_lop IS NOT NULL AND role = 'student' ORDER BY ten_lop");
$major_list = fetchAll($conn, "SELECT * FROM majors ORDER BY ten_nganh");

// (Xử lý xóa user - giữ nguyên)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "Không thể xóa tài khoản đang đăng nhập!";
    } else {
        $delete_sql = "DELETE FROM users WHERE id = ? AND role != 'admin'";
        if (executeQuery($conn, $delete_sql, "i", [$user_id])) {
            $_SESSION['success'] = "Xóa người dùng thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra!";
        }
    }
    header("Location: users.php?role=" . urlencode($filter_role) . "&lop=" . urlencode($filter_lop) . "&major_id=" . $filter_major);
    exit();
}

// (Câu SQL động - giữ nguyên)
$sql = "SELECT u.*, m.ten_nganh 
        FROM users u 
        LEFT JOIN majors m ON u.major_id = m.id 
        WHERE u.role != 'admin'";
$params = [];
$types = "";

if (!empty($filter_role)) {
    $sql .= " AND u.role = ?";
    $params[] = $filter_role;
    $types .= "s";
}
if (!empty($filter_lop) && $filter_role == 'student') { // Chỉ lọc lớp nếu vai trò là sinh viên
    $sql .= " AND u.ten_lop = ?";
    $params[] = $filter_lop;
    $types .= "s";
}
if ($filter_major > 0) {
    $sql .= " AND u.major_id = ?";
    $params[] = $filter_major;
    $types .= "i";
}
$sql .= " ORDER BY u.role, u.full_name";
$users = fetchAll($conn, $sql, $types, $params);
// ====================================

require_once '../includes/header.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header bg-light py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-filter me-2"></i>Bộ lọc tìm kiếm</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="users.php" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Lọc theo vai trò</label>
                    <select name="role" id="roleFilter" class="form-select filter-select">
                        <option value="">-- Tất cả (Trừ Admin) --</option>
                        <option value="teacher" <?php echo ($filter_role == 'teacher') ? 'selected' : ''; ?>>Chỉ Giảng viên</option>
                        <option value="student" <?php echo ($filter_role == 'student') ? 'selected' : ''; ?>>Chỉ Sinh viên</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Lọc theo ngành</label>
                    <select name="major_id" class="form-select filter-select">
                        <option value="">-- Tất cả các ngành --</option>
                        <?php foreach ($major_list as $major): ?>
                            <option value="<?php echo $major['id']; ?>" <?php echo ($filter_major == $major['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($major['ten_nganh']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3" id="lopWrapper" style="display: <?php echo ($filter_role == 'student') ? 'block' : 'none'; ?>;">
                    <label class="form-label fw-bold">Lọc theo lớp (cho Sinh viên)</label>
                    <select name="lop" class="form-select filter-select">
                        <option value="">-- Tất cả các lớp --</option>
                        <?php foreach ($lop_list as $lop): ?>
                            <option value="<?php echo htmlspecialchars($lop['ten_lop']); ?>" <?php echo ($filter_lop == $lop['ten_lop']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lop['ten_lop']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type"submit" id="filterButton" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i> Lọc</button>
                    <a href="users.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Xóa lọc</a>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Danh Sách Người Dùng</h5>
        <a href="user_add.php" class="btn btn-light btn-sm">
            <i class="fas fa-user-plus me-1"></i>Thêm Người Dùng
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Họ Tên</th>
                        <th>Vai Trò</th>
                        <th>Ngành Học</th>
                        <th>Tên Lớp</th>
                        <th>Ngày Tạo</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="text-center p-5">
                                    <?php if (empty($filter_role) && empty($filter_lop) && empty($filter_major)): ?>
                                        <i class="fas fa-user-plus fa-4x text-muted mb-3"></i>
                                        <h5 class="fw-bold">Chưa có người dùng nào (GV/SV)</h5>
                                        <p class="text-muted">Hãy bắt đầu bằng cách thêm người dùng đầu tiên.</p>
                                        <a href="user_add.php" class="btn btn-primary btn-lg mt-2">
                                            <i class="fas fa-user-plus me-1"></i> Thêm Người Dùng
                                        </a>
                                    <?php else: ?>
                                        <i class="fas fa-filter fa-4x text-muted mb-3"></i>
                                        <h5 class="fw-bold">Không tìm thấy kết quả</h5>
                                        <p class="text-muted">Không có người dùng nào khớp với bộ lọc của bạn.</p>
                                        <a href="users.php" class="btn btn-outline-secondary mt-2">
                                            <i class="fas fa-times me-1"></i> Xóa bộ lọc
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td>
                                <?php if ($user['role'] == 'teacher'): ?>
                                    <span class="badge bg-success-subtle text-success-emphasis">Giảng viên</span>
                                <?php else: ?>
                                    <span class="badge bg-info-subtle text-info-emphasis">Sinh viên</span>
                                <?php endif; ?>
                                </td>
                            <td><?php echo htmlspecialchars($user['ten_nganh']); ?></td>
                            <td><?php echo htmlspecialchars($user['ten_lop']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td class="text-center">
                                <a href="user_edit.php?id=<?php echo $user['id']; ?>" 
                                   class="btn btn-sm btn-info" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo urlencode($filter_role); ?>', '<?php echo urlencode($filter_lop); ?>', '<?php echo $filter_major; ?>')" 
                                        class="btn btn-sm btn-danger" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function deleteUser(userId, role, lop, major) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: 'Tất cả đặt phòng của người dùng này cũng sẽ bị xóa!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `users.php?delete=${userId}&role=${role}&lop=${lop}&major_id=${major}`;
        }
    });
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const roleFilter = document.getElementById('roleFilter');
    const lopWrapper = document.getElementById('lopWrapper');
    
    document.getElementById('filterButton').style.display = 'none';

    function toggleLopFilter() {
        if (roleFilter.value === 'student') {
            lopWrapper.style.display = 'block';
        } else {
            lopWrapper.style.display = 'none';
        }
    }
    toggleLopFilter();

    const allFilters = document.querySelectorAll('.filter-select');
    allFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            if (this.id === 'roleFilter') {
                toggleLopFilter();
            }
            filterForm.submit();
        });
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>