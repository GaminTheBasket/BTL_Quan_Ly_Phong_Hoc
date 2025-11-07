<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Duyệt Đặt Phòng";

// Lấy danh sách yêu cầu (Phần này giữ nguyên)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'pending';

$where_clause = "WHERE b.status = ?";
$params = [$filter];
$types = "s";

if ($filter == 'all') {
    $where_clause = "";
    $params = [];
    $types = "";
}

$sql = "SELECT b.*, u.full_name, u.role, r.room_name
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        $where_clause
        ORDER BY b.created_at DESC";

$bookings = fetchAll($conn, $sql, $types, $params);

require_once '../includes/header.php';
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $filter == 'pending' ? 'active' : ''; ?>" 
           href="?filter=pending">
            <i class="fas fa-clock me-1"></i>Chờ Duyệt
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter == 'approved' ? 'active' : ''; ?>" 
           href="?filter=approved">
            <i class="fas fa-check me-1"></i>Đã Duyệt
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter == 'rejected' ? 'active' : ''; ?>" 
           href="?filter=rejected">
            <i class="fas fa-times me-1"></i>Đã Từ Chối
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $filter == 'all' ? 'active' : ''; ?>" 
           href="?filter=all">
            <i class="fas fa-list me-1"></i>Tất Cả
        </a>
    </li>
</ul>

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>
            <?php 
            $titles = [
                'pending' => 'Yêu Cầu Chờ Duyệt',
                'approved' => 'Yêu Cầu Đã Duyệt',
                'rejected' => 'Yêu Cầu Đã Từ Chối',
                'all' => 'Tất Cả Yêu Cầu'
            ];
            echo $titles[$filter];
            ?>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($bookings)): ?>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>Không có yêu cầu nào.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Người Đặt</th>
                            <th>Phòng</th>
                            <th>Thời Gian</th>
                            <th>Mục Đích</th>
                            <th>Trạng Thái</th>
                            <th class="text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                        <tr id="booking-row-<?php echo $booking['id']; ?>">
                            <td><?php echo $booking['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    <?php echo $booking['role'] == 'teacher' ? 'Giảng viên' : 'Sinh viên'; ?>
                                </small>
                            </td>
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
                                ?>
                                <span class="badge <?php echo $badge_class[$booking['status']]; ?> badge-status" 
                                      id="status-badge-<?php echo $booking['id']; ?>">
                                    <?php echo $text[$booking['status']]; ?>
                                </span>
                                </td>
                            <td class="text-center">
                                <div id="action-buttons-<?php echo $booking['id']; ?>">
                                    <?php if ($booking['status'] == 'pending'): ?>
                                        <button onclick="handleBooking(<?php echo $booking['id']; ?>, 'approve')" 
                                                class="btn btn-sm btn-success" title="Duyệt">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="handleBooking(<?php echo $booking['id']; ?>, 'reject')" 
                                                class="btn btn-sm btn-danger" title="Từ chối">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </div>
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
async function handleBooking(bookingId, action) {
    const actionText = (action === 'approve') ? 'Duyệt' : 'Từ chối';
    const actionColor = (action === 'approve') ? '#1cc88a' : '#e74a3b';

    const result = await Swal.fire({
        title: `${actionText} yêu cầu này?`,
        text: 'Bạn có chắc chắn muốn thực hiện thao tác này?',
        icon: (action === 'approve') ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: actionColor,
        cancelButtonColor: '#858796',
        confirmButtonText: actionText,
        cancelButtonText: 'Hủy'
    });

    if (!result.isConfirmed) {
        return;
    }

    const buttonWrapper = document.getElementById(`action-buttons-${bookingId}`);
    const statusBadge = document.getElementById(`status-badge-${bookingId}`);
    
    buttonWrapper.innerHTML = `
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
    `;

    try {
        const response = await fetch('booking_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: bookingId,
                action: action
            })
        });
        
        const data = await response.json();

        if (data.success) {
            
            // === SỬA MÀU SẮC TRONG JAVASCRIPT ===
            if (data.new_status === 'approved') {
                statusBadge.textContent = 'Đã duyệt';
                statusBadge.className = 'badge bg-success-subtle text-success-emphasis badge-status';
            } else {
                statusBadge.textContent = 'Từ chối';
                statusBadge.className = 'badge bg-danger-subtle text-danger-emphasis badge-status';
            }
            // === KẾT THÚC SỬA ===
            
            buttonWrapper.innerHTML = `<span class="text-muted">-</span>`;
            
            Swal.fire({
                title: 'Thành công!',
                text: data.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });

            const currentFilter = '<?php echo $filter; ?>';
            if (currentFilter === 'pending') {
                document.getElementById(`booking-row-${bookingId}`).style.opacity = '0.5';
            }

        } else {
            throw new Error(data.message);
        }

    } catch (error) {
        Swal.fire('Có lỗi!', error.message || 'Không thể kết nối.', 'error');
        buttonWrapper.innerHTML = `
            <button onclick="handleBooking(${bookingId}, 'approve')" class="btn btn-sm btn-success" title="Duyệt">
                <i class="fas fa-check"></i>
            </button>
            <button onclick="handleBooking(${bookingId}, 'reject')" class="btn btn-sm btn-danger" title="Từ chối">
                <i class="fas fa-times"></i>
            </button>
        `;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>