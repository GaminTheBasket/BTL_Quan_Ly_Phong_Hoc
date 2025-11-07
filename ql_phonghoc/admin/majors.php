<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Quản lý Ngành học";

// Xử lý xóa
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $major_id = intval($_GET['delete']);
    
    $delete_sql = "DELETE FROM majors WHERE id = ?";
    if (executeQuery($conn, $delete_sql, "i", [$major_id])) {
        $_SESSION['success'] = "Xóa ngành học thành công!";
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra!";
    }
    header("Location: majors.php");
    exit();
}

// Lấy danh sách ngành học
$majors = fetchAll($conn, "SELECT * FROM majors ORDER BY ten_nganh");

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

<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-university me-2"></i>Danh Sách Ngành Học</h5>
        <a href="major_add.php" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-1"></i>Thêm Ngành Học
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Mã Ngành</th>
                        <th>Tên Ngành Học</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($majors)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="text-center p-5">
                                    <i class="fas fa-graduation-cap fa-4x text-muted mb-3"></i>
                                    <h5 class="fw-bold">Chưa có ngành học nào</h5>
                                    <p class="text-muted">Hãy bắt đầu bằng cách thêm ngành học đầu tiên.</p>
                                    <a href="major_add.php" class="btn btn-primary btn-lg mt-2">
                                        <i class="fas fa-plus me-1"></i> Thêm Ngành Học
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($majors as $major): ?>
                        <tr>
                            <td><?php echo $major['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($major['ma_nganh']); ?></strong></td>
                            <td><?php echo htmlspecialchars($major['ten_nganh']); ?></td>
                            <td class="text-center">
                                <a href="major_edit.php?id=<?php echo $major['id']; ?>" 
                                   class="btn btn-sm btn-info" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteMajor(<?php echo $major['id']; ?>)" 
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
function deleteMajor(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: 'Người dùng (sinh viên/giảng viên) thuộc ngành này sẽ không bị mất, nhưng sẽ bị gỡ liên kết.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'majors.php?delete=' + id;
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>