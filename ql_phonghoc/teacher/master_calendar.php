<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Lịch Tổng Quan Các Phòng";
require_once '../includes/header.php'; // Nạp header (sidebar)
?>

<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-calendar-alt me-2"></i>
            Lịch Tổng Quan (Tất cả các phòng đã được duyệt)
        </h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Lịch này hiển thị tất cả các lịch đã được duyệt của <strong>tất cả các phòng</strong>. Các ô màu xám là thời gian đã bị đặt. Các ô màu trắng là thời gian còn trống.
        </div>
        <div id="masterCalendar"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('masterCalendar');
    
    // Gọi file API mới
    fetch('get_all_bookings.php')
        .then(response => response.json())
        .then(bookings => {
            
            const events = bookings.map(booking => ({
                // Hiển thị cả tên phòng và mục đích
                title: `${booking.room_name}: ${booking.purpose}`, 
                start: booking.start_time,
                end: booking.end_time,
                // Hiển thị màu xám, vì đây là lịch "chỉ xem"
                backgroundColor: '#858796', 
                borderColor: '#858796'
            }));
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'vi',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                slotMinTime: '07:00:00',
                slotMaxTime: '22:00:00',
                events: events,
                // Không cho phép bấm vào ô trống (vì đây là trang xem)
                selectable: false, 
                // Hiển thị chi tiết khi bấm vào lịch
                eventClick: function(info) {
                    Swal.fire({
                        title: info.event.title,
                        html: `
                            <div style="text-align: left; margin-top: 20px;">
                                <p><strong><i class="fas fa-door-open me-2"></i>Phòng học:</strong> ${info.event.title.split(':')[0]}</p>
                                <p><strong><i class="fas
                                fa-chalkboard-teacher me-2"></i>Môn/Mục đích:</strong> ${info.event.title.split(': ')[1]}</p>
                                <p><strong><i class="fas fa-user me-2"></i>Giảng viên đặt:</strong> ${info.event.extendedProps.teacher}</p>
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

<?php require_once '../includes/footer.php'; ?>