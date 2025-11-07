<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Dashboard Giảng Viên";
$user_id = $_SESSION['user_id'];

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming'; 

$stats = [];
$stats['pending'] = fetchOne($conn, 
    "SELECT COUNT(*) as total FROM bookings WHERE user_id = ? AND status = 'pending'", 
    "i", [$user_id]
)['total'];
$stats['upcoming'] = fetchOne($conn, 
    "SELECT COUNT(*) as total FROM bookings WHERE user_id = ? AND status = 'approved' AND start_time > NOW()", 
    "i", [$user_id]
)['total'];
$stats['approved_total'] = fetchOne($conn, 
    "SELECT COUNT(*) as total FROM bookings WHERE user_id = ? AND status = 'approved'", 
    "i", [$user_id]
)['total'];


$table_title = "Lịch Sử Đặt Phòng";
$sql_where = "WHERE b.user_id = ?";
$params = [$user_id];
$types = "i";

switch ($filter) {
    case 'pending':
        $table_title = "Danh sách Lịch Chờ Duyệt";
        $sql_where .= " AND b.status = 'pending'";
        break;
    case 'upcoming':
        $table_title = "Danh sách Lịch Sắp Tới (Đã duyệt)";
        $sql_where .= " AND b.status = 'approved' AND b.start_time > NOW()";
        break;
    case 'approved_total':
        $table_title = "Toàn bộ Lịch Đã Duyệt (Cả quá khứ)";
        $sql_where .= " AND b.status = 'approved'";
        break;
    default: 
        $table_title = "Toàn Bộ Lịch Sử Đặt Phòng";
        break;
}

// === SỬA LỖI TẠI DÒNG DƯỚI ===
// Xóa dấu ... (ba chấm) khỏi ...$params
$bookings = fetchAll($conn, "
    SELECT b.*, r.room_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    $sql_where
    ORDER BY b.start_time DESC
    LIMIT 50
", $types, $params); // ĐÃ XÓA ...

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <a href="index.php?filter=pending" class="text-decoration-none">
            <div class="card stat-card border-left-warning h-100 py-2 <?php echo ($filter == 'pending') ? 'shadow-lg' : 'shadow'; ?>" style="border-left: 4px solid #f6c23e;">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Chờ Duyệt</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending']; ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-clock fa-2x text-warning stat-icon"></i></div>
                </div></div>
            </div>
        </a>
    </div>
    <div class="col-md-4 mb-3">
        <a href="index.php?filter=upcoming" class="text-decoration-none">
            <div class="card stat-card border-left-primary h-100 py-2 <?php echo ($filter == 'upcoming') ? 'shadow-lg' : 'shadow'; ?>" style="border-left: 4px solid #4e73df;">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Lịch Sắp Tới</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['upcoming']; ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-primary stat-icon"></i></div>
                </div></div>
            </div>
        </a>
    </div>
    <div class="col-md-4 mb-3">
        <a href="index.php?filter=approved_total" class="text-decoration-none">
            <div class="card stat-card border-left-success h-100 py-2 <?php echo ($filter == 'approved_total') ? 'shadow-lg' : 'shadow'; ?>" style="border-left: 4px solid #1cc88a;">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tổng Đã Duyệt</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['approved_total']; ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-check-circle fa-2x text-success stat-icon"></i></div>
                </div></div>
            </div>
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow border-left-primary" style="border-left: 4px solid #4e73df;">
            <div class="card-body text-center py-4">
                <h4 class="mb-3"><i class="fas fa-plus-circle text-primary me-2"></i>Đặt Phòng Mới</h4>
                <p class="text-muted mb-3">Tìm kiếm và đặt phòng học cho lịch giảng dạy của bạn</p>
                <a href="booking_new.php" class="btn btn-primary btn-lg btn-custom">
                    <i class="fas fa-calendar-plus me-2"></i>Bắt Đầu Đặt Phòng
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i><?php echo $table_title; ?></h5>
        <?php if ($filter != 'all'): ?>
            <a href="index.php?filter=all" class="btn btn-light btn-sm">
                <i class="fas fa-list me-1"></i> Xem Tất Cả
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (empty($bookings)): ?>
            <div class="text-center p-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5 class="fw-bold">Không tìm thấy lịch nào</h5>
                <?php if ($filter == 'pending'): ?>
                    <p class="text-muted">Bạn không có lịch nào đang chờ duyệt.</p>
                <?php elseif ($filter == 'upcoming'): ?>
                    <p class="text-muted">Bạn không có lịch học nào sắp tới.</p>
                <?php else: ?>
                    <p class="text-muted">Bạn chưa đặt phòng nào.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Phòng</th>
                            <th>Thời Gian</th>
                            <th>Mục Đích</th>
                            <th>Trạng Thái</th>
                            <th class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($booking['room_name']); ?></strong></td>
                            <td>
                                <div><?php echo date('d/m/Y', strtotime($booking['start_time'])); ?></div>
                                <small class="text-muted">
                                    <?php echo date('H:i', strtotime($booking['start_time'])); ?> - 
                                    <?php echo date('H:i', strtotime($booking['end_time'])); ?>
                                </small>
                            </td>
                            <td><?php echo htmlspecialchars($booking['purpose']); ?></td>
                            <td>
                                <?php
                                $badge_class = [
                                    'pending' => 'bg-warning-subtle text-warning-emphasis',
                                    'approved' => 'bg-success-subtle text-success-emphasis',
                                    'rejected' => 'bg-danger-subtle text-danger-emphasis'
                                ];
                                $text = [
                                    'pending' => 'Chờ duyệt',
                                    'approved' => 'Đã duyệt',
                                    'rejected' => 'Từ chối'
                                ];
                                $icon = [
                                    'pending' => 'clock',
                                    'approved' => 'check-circle',
                                    'rejected' => 'times-circle'
                                ];
                                ?>
                                <span class="badge <?php echo $badge_class[$booking['status']]; ?> badge-status">
                                    <i class="fas fa-<?php echo $icon[$booking['status']]; ?> me-1"></i>
                                    <?php echo $text[$booking['status']]; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php 
                                $can_cancel = ($booking['status'] == 'pending') || 
                                              ($booking['status'] == 'approved' && strtotime($booking['start_time']) > time());
                                
                                if ($can_cancel): 
                                ?>
                                    <button onclick="cancelBooking(<?php echo $booking['id']; ?>)" 
                                            class="btn btn-sm btn-danger" title="Hủy">
                                        <i class="fas fa-times-circle"></i> Hủy
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function cancelBooking(id) {
    Swal.fire({
        title: 'Hủy đặt phòng?',    
        text: 'Bạn có chắc chắn muốn hủy yêu cầu này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Hủy đặt phòng',
        cancelButtonText: 'Không'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'booking_cancel.php?id=' + id;
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>