<?php
session_start();
require_once '../includes/db_connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Dashboard";

// Lấy thống kê cho 4 thẻ
$stats = [];
$result = $conn->query("SELECT COUNT(*) as total FROM rooms");
$stats['total_rooms'] = $result->fetch_assoc()['total'];
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
$stats['total_users'] = $result->fetch_assoc()['total'];
$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
$stats['pending_bookings'] = $result->fetch_assoc()['total'];
$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE DATE(start_time) = CURDATE() AND status = 'approved'");
$stats['today_bookings'] = $result->fetch_assoc()['total'];

// Lấy 2 bảng
$recent_bookings = fetchAll($conn, "SELECT b.*, u.full_name, r.room_name FROM bookings b JOIN users u ON b.user_id = u.id JOIN rooms r ON b.room_id = r.id ORDER BY b.created_at DESC LIMIT 10");
$today_schedule = fetchAll($conn, "SELECT b.*, r.room_name, u.full_name FROM bookings b JOIN rooms r ON b.room_id = r.id JOIN users u ON b.user_id = u.id WHERE DATE(b.start_time) = CURDATE() AND b.status = 'approved' ORDER BY b.start_time");


// === 1. PHP LẤY DỮ LIỆU CHO BIỂU ĐỒ ===

// A. Biểu đồ Cột (Top 7 phòng)
$chart_data_sql = "
    SELECT r.room_name, COUNT(b.id) as booking_count
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.status = 'approved'
    GROUP BY r.room_name
    ORDER BY booking_count DESC
    LIMIT 7
";
$chart_data_bar = fetchAll($conn, $chart_data_sql);
$chart_labels_bar = [];
$chart_values_bar = [];
foreach ($chart_data_bar as $data) {
    $chart_labels_bar[] = $data['room_name'];
    $chart_values_bar[] = $data['booking_count'];
}
$chart_labels_bar_json = json_encode($chart_labels_bar);
$chart_values_bar_json = json_encode($chart_values_bar);


// B. SỬA: Biểu đồ Đường (Xu hướng 7 ngày)
$chart_line_labels = [];
$chart_line_values = [];
$bookings_by_day = [];

// 1. Lấy số lượt đặt phòng mới (bất kể trạng thái) trong 7 ngày qua
$line_data_sql = "
    SELECT 
        DATE(created_at) as creation_date, 
        COUNT(id) as booking_count
    FROM bookings
    WHERE created_at >= CURDATE() - INTERVAL 7 DAY
    GROUP BY creation_date
    ORDER BY creation_date ASC
";
$line_data = fetchAll($conn, $line_data_sql);

// 2. Đưa kết quả DB vào mảng để tra cứu
foreach ($line_data as $data) {
    $bookings_by_day[$data['creation_date']] = $data['booking_count'];
}

// 3. Tạo 7 ngày nhãn (labels) và dữ liệu (data), điền 0 nếu không có
for ($i = 6; $i >= 0; $i--) {
    $date_obj = new DateTime("-$i days");
    $date_key = $date_obj->format('Y-m-d'); // Key để tra cứu
    $date_label = $date_obj->format('d/m'); // Nhãn cho biểu đồ
    
    $chart_line_labels[] = $date_label;
    $chart_line_values[] = $bookings_by_day[$date_key] ?? 0;
}

$chart_labels_line_json = json_encode($chart_line_labels);
$chart_values_line_json = json_encode($chart_line_values);
// === KẾT THÚC KHỐI PHP SỬA ===


require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="rooms.php" class="text-decoration-none">
            <div class="card stat-card border-left-primary h-100 py-2 shadow-sm" style="border-left: 4px solid #4e73df;">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng Phòng Học</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_rooms']; ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-door-open fa-2x text-primary stat-icon"></i></div>
                </div></div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="users.php" class="text-decoration-none">
            <div class="card stat-card border-left-success h-100 py-2 shadow-sm" style="border-left: 4px solid #1cc88a;">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tổng Người Dùng</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-users fa-2x text-success stat-icon"></i></div>
                </div></div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="bookings.php?filter=pending" class="text-decoration-none">
            <div class="card stat-card border-left-warning h-100 py-2 shadow-sm" style="border-left: 4px solid #f6c23e;">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Chờ Duyệt</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['pending_bookings']; ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-clock fa-2x text-warning stat-icon"></i></div>
                </div></div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left-info h-100 py-2 shadow-sm" style="border-left: 4px solid #36b9cc;">
            <div class="card-body"><div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Lịch Học Hôm Nay</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['today_bookings']; ?></div>
                </div>
                <div class="col-auto"><i class="fas fa-calendar-day fa-2x text-info stat-icon"></i></div>
            </div></div>
        </div>
    </div>
</div>


<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Mức Độ Sử Dụng Phòng</h5>
            </div>
            <div class="card-body">
                <canvas id="roomUsageChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Xu Hướng Đặt Phòng (7 Ngày Qua)</h5>
            </div>
            <div class="card-body">
                <canvas id="bookingTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Lịch Phòng Hôm Nay (Đã duyệt)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($today_schedule)): ?>
                    <div class="alert alert-info mb-0">Không có lịch đặt phòng nào hôm nay.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">...</thead>
                            <tbody>
                                <?php foreach ($today_schedule as $schedule): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($schedule['room_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($schedule['full_name']); ?></td>
                                    <td><?php echo date('H:i', strtotime($schedule['start_time'])) . ' - ' . date('H:i', strtotime($schedule['end_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['purpose']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Yêu Cầu Đặt Phòng Gần Đây</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">...</thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                <td><strong><?php echo htmlspecialchars($booking['room_name']); ?></strong></td>
                                <td><small><?php echo date('d/m/Y H:i', strtotime($booking['start_time'])); ?></small></td>
                                <td><?php echo htmlspecialchars($booking['purpose']); ?></td>
                                <td>
                                    <?php
                                    $badge_class = ['pending' => 'bg-warning-subtle text-warning-emphasis', 'approved' => 'bg-success-subtle text-success-emphasis', 'rejected' => 'bg-danger-subtle text-danger-emphasis'];
                                    $status_text = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'];
                                    ?>
                                    <span class="badge <?php echo $badge_class[$booking['status']]; ?> badge-status">
                                        <?php echo $status_text[$booking['status']]; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Vẽ Biểu đồ Cột (Bar Chart) - Giữ nguyên
    if (document.getElementById('roomUsageChart')) {
        const ctxBar = document.getElementById('roomUsageChart').getContext('2d');
        const chartLabelsBar = <?php echo $chart_labels_bar_json; ?>;
        const chartValuesBar = <?php echo $chart_values_bar_json; ?>;

        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: chartLabelsBar,
                datasets: [{
                    label: 'Số lượt đặt (đã duyệt)',
                    data: chartValuesBar,
                    backgroundColor: 'rgba(78, 115, 223, 0.8)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // 2. Sửa: Vẽ Biểu đồ Đường (Line Chart)
    if (document.getElementById('bookingTrendChart')) {
        const ctxLine = document.getElementById('bookingTrendChart').getContext('2d');
        const chartLabelsLine = <?php echo $chart_labels_line_json; ?>;
        const chartValuesLine = <?php echo $chart_values_line_json; ?>;

        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: chartLabelsLine,
                datasets: [{
                    label: 'Lượt đặt phòng mới',
                    data: chartValuesLine,
                    backgroundColor: 'rgba(54, 185, 204, 0.1)', // Màu xanh Info (nhạt)
                    borderColor: 'rgba(54, 185, 204, 1)', // Màu xanh Info (đậm)
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3 // Làm mượt đường
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
<?php require_once '../includes/footer.php'; ?>