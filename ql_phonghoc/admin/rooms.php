<?php
session_start();
require_once '../includes/db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Quản lý Phòng";

// Xử lý xóa phòng
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $room_id = intval($_GET['delete']);
    
    // Kiểm tra xem phòng có đặt chỗ không
    $check_sql = "SELECT COUNT(*) as count FROM bookings WHERE room_id = ? AND status != 'rejected'";
    $check = fetchOne($conn, $check_sql, "i", [$room_id]);
    
    if ($check['count'] > 0) {
        $_SESSION['error'] = "Không thể xóa phòng đang có lịch đặt!";
    } else {
        $delete_sql = "DELETE FROM rooms WHERE id = ?";
        if (executeQuery($conn, $delete_sql, "i", [$room_id])) {
            $_SESSION['success'] = "Xóa phòng thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi xóa phòng!";
        }
    }
    
    header("Location: rooms.php");
    exit();
}

// Lấy danh sách tất cả phòng
$rooms = fetchAll($conn, "SELECT * FROM rooms ORDER BY room_name");

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

<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-door-open me-2"></i>Danh Sách Phòng Học</h5>
        <a href="room_add.php" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-1"></i>Thêm Phòng Mới
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="roomsTable">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tên Phòng</th>
                        <th>Sức Chứa</th>
                        <th>Thiết Bị</th>
                        <th>Trạng Thái</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="text-center p-5">
                                    <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                    <h5 class="fw-bold">Chưa có phòng học nào</h5>
                                    <p class="text-muted">Hãy bắt đầu bằng cách thêm phòng học đầu tiên của bạn.</p>
                                    <a href="room_add.php" class="btn btn-primary btn-lg mt-2">
                                        <i class="fas fa-plus me-1"></i> Thêm Phòng Mới
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?php echo $room['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($room['room_name']); ?></strong></td>
                            <td>
                                <i class="fas fa-users text-muted me-1"></i>
                                <?php echo $room['capacity']; ?> người
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars(substr($room['equipment'], 0, 50)); ?>
                                    <?php if (strlen($room['equipment']) > 50) echo '...'; ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($room['status'] == 'available'): ?>
                                    <span class="badge bg-success-subtle text-success-emphasis badge-status">
                                        <i class="fas fa-check-circle me-1"></i>Sẵn sàng
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis badge-status">
                                        <i class="fas fa-tools me-1"></i>Bảo trì
                                    </span>
                                <?php endif; ?>
                                </td>
                            <td class="text-center">
                                <a href="room_edit.php?id=<?php echo $room['id']; ?>" 
                                   class="btn btn-sm btn-info btn-custom" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="deleteRoom(<?php echo $room['id']; ?>)" 
                                        class="btn btn-sm btn-danger btn-custom" title="Xóa">
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
function deleteRoom(roomId) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: 'Bạn có chắc chắn muốn xóa phòng này? Hành động này không thể hoàn tác!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'rooms.php?delete=' + roomId;
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>