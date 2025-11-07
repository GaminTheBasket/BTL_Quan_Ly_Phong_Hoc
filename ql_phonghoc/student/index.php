<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Tra Cứu Lịch Học";

// Lấy thông tin lớp của sinh viên
$student = fetchOne($conn, "SELECT ten_lop FROM users WHERE id = ?", "i", [$_SESSION['user_id']]);
$ten_lop = $student ? $student['ten_lop'] : null;

require_once '../includes/header.php';
?>

<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-search me-2"></i>
            Lịch Học Của Bạn
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($ten_lop)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Tài khoản của bạn chưa được gán vào lớp nào. Vui lòng liên hệ admin.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Hiển thị lịch học (đã được duyệt) của lớp: <strong><?php echo htmlspecialchars($ten_lop); ?></strong>
            </div>
            <div id="studentCalendar"></div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($ten_lop)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('studentCalendar');
    
    fetch('get_schedule.php')
        .then(response => response.json())
        .then(bookings => {
            const events = bookings.map(booking => ({
                title: `${booking.room_name}: ${booking.purpose}`,
                start: booking.start_time,
                end: booking.end_time,
                backgroundColor: '#1cc88a',
                borderColor: '#1cc88a',
                extendedProps: {
                    teacher: booking.teacher_name
                }
            }));
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'vi',
                
                // === THÊM DÒNG NÀY ĐỂ SỬA LỖI ===
                height: 'auto',
                // === KẾT THÚC SỬA ===
                
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
                slotMinTime: '07:00:00',
                slotMaxTime: '22:00:00',
                events: events,
                selectable: false,
                eventClick: function(info) {
                    Swal.fire({
                        title: info.event.title,
                        html: `
                            <div style="text-align: left; margin-top: 20px;">
                                <p><strong><i class="fas fa-chalkboard-teacher me-2"></i>Môn học:</strong> ${info.event.title.split(': ')[1]}</p>
                                <p><strong><i class="fas fa-user me-2"></i>Giảng viên:</strong> ${info.event.extendedProps.teacher}</p>
                                <p><strong><i class="fas fa-clock me-2"></i>Thời gian:</strong> ${info.event.start.toLocaleString('vi-VN')} - ${info.event.end.toLocaleString('vi-VN')}</p>
                            </div>
                        `,
                        icon: 'info'
                    });
                }
            });
            
            calendar.render();
        });
});
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>