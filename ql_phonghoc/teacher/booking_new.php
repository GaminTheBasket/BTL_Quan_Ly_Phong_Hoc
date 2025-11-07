<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}

$page_title = "Đặt Phòng Mới";

// === NÂNG CẤP: LẤY DANH SÁCH MÔN HỌC VÀ LỚP ===
// 1. Lấy danh sách môn học
$sql_subjects = "SELECT * FROM subjects ORDER BY ten_mon_hoc";
$danh_sach_mon_hoc = fetchAll($conn, $sql_subjects);

// 2. Lấy danh sách lớp
$sql_lop = "SELECT DISTINCT ten_lop FROM users WHERE ten_lop IS NOT NULL AND role = 'student' ORDER BY ten_lop";
$danh_sach_lop = fetchAll($conn, $sql_lop);
// ============================================

// Xử lý form đặt phòng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_id = intval($_POST['room_id']);
    $start_time_str = $_POST['start_time'];
    $end_time_str = $_POST['end_time'];
    $ten_lop = trim($_POST['ten_lop']);
    $user_id = $_SESSION['user_id'];
    
    // === NÂNG CẤP: XỬ LÝ MỤC ĐÍCH / MÔN HỌC ===
    $subject_id = intval($_POST['subject_id']);
    $purpose = trim($_POST['purpose']); // Lấy từ ô "Mục đích khác"

    // Nếu không chọn môn học (chọn "Khác"), thì $subject_id = 0
    if ($subject_id == 0) {
        $subject_id = NULL; // Lưu NULL vào CSDL
        if (empty($purpose)) {
             $_SESSION['error'] = "Bạn đã chọn 'Khác', vui lòng nhập mục đích!";
        }
    } else {
        // Nếu đã chọn môn học, lấy tên môn học làm mục đích
        $subject_info = fetchOne($conn, "SELECT ten_mon_hoc FROM subjects WHERE id = ?", "i", [$subject_id]);
        $purpose = $subject_info['ten_mon_hoc'];
    }
    // ============================================

    // (Code sửa lỗi ngày tháng giữ nguyên...)
    $start_dt = DateTime::createFromFormat('Y-m-d\TH:i', $start_time_str);
    if (!$start_dt) { $start_dt = DateTime::createFromFormat('d/m/Y h:i A', $start_time_str); }
    $end_dt = DateTime::createFromFormat('Y-m-d\TH:i', $end_time_str);
    if (!$end_dt) { $end_dt = DateTime::createFromFormat('d/m/Y h:i A', $end_time_str); }
    
    $start_timestamp = $start_dt ? $start_dt->getTimestamp() : false;
    $end_timestamp = $end_dt ? $end_dt->getTimestamp() : false;
    
    // Validate
    if (empty($room_id) || empty($start_time_str) || empty($end_time_str) || empty($purpose)) {
        $_SESSION['error'] = "Vui lòng điền đầy đủ thông tin!";
    } elseif (!$start_timestamp || !$end_timestamp) {
        $_SESSION['error'] = "Định dạng ngày giờ không hợp lệ! Vui lòng chọn lại.";
    } elseif ($end_timestamp <= $start_timestamp) {
        $_SESSION['error'] = "Thời gian kết thúc phải sau thời gian bắt đầu!";
    } elseif ($start_timestamp < time()) {
        $_SESSION['error'] = "Không thể đặt phòng cho quá khứ!";
    } else if (!isset($_SESSION['error'])) { // Chỉ chạy nếu không có lỗi nào ở trên
        
        $start_time_db = $start_dt->format('Y-m-d H:i:s');
        $end_time_db = $end_dt->format('Y-m-d H:i:s');

        // (Code kiểm tra xung đột giữ nguyên...)
        $check_sql = "SELECT id FROM bookings 
                      WHERE room_id = ? AND status != 'rejected'
                      AND (
                          (start_time <= ? AND end_time > ?) OR
                          (start_time < ? AND end_time >= ?) OR
                          (start_time >= ? AND end_time <= ?)
                      )";
        $conflict = fetchOne($conn, $check_sql, "issssss", [
            $room_id, $start_time_db, $start_time_db, $end_time_db, $end_time_db, $start_time_db, $end_time_db
        ]);
        
        if ($conflict) {
            $_SESSION['error'] = "Phòng đã được đặt trong khoảng thời gian này!";
        } else {
            // === NÂNG CẤP: LƯU CẢ subject_id ===
            $insert_sql = "INSERT INTO bookings (user_id, room_id, start_time, end_time, purpose, ten_lop, subject_id, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
            if (executeQuery($conn, $insert_sql, "iissssi", [$user_id, $room_id, $start_time_db, $end_time_db, $purpose, $ten_lop, $subject_id])) {
                $_SESSION['success'] = "Gửi yêu cầu đặt phòng thành công! Vui lòng chờ admin duyệt.";
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra!";
            }
        }
    }
}

// Lấy danh sách phòng có sẵn
$rooms = fetchAll($conn, "SELECT * FROM rooms WHERE status = 'available' ORDER BY room_name");

require_once '../includes/header.php';
?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Chọn Phòng</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tìm theo sức chứa</label>
                    <select id="capacityFilter" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="30">≥ 30 người</option>
                        <option value="50">≥ 50 người</option>
                        <option value="80">≥ 80 người</option>
                    </select>
                </div>
                
                <div class="list-group" id="roomList">
                    <?php foreach ($rooms as $room): ?>
                    <a href="#" class="list-group-item list-group-item-action room-item" 
                       data-room-id="<?php echo $room['id']; ?>"
                       data-room-name="<?php echo htmlspecialchars($room['room_name']); ?>"
                       data-capacity="<?php echo $room['capacity']; ?>">
                       <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($room['room_name']); ?></h6>
                            <small class="text-muted">
                                <i class="fas fa-users"></i> <?php echo $room['capacity']; ?>
                            </small>
                        </div>
                        <small class="text-muted">
                            <?php echo htmlspecialchars(substr($room['equipment'], 0, 50)); ?>
                            <?php if (strlen($room['equipment']) > 50) echo '...'; ?>
                        </small>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Lịch Phòng</h5>
            </div>
            <div class="card-body">
                <div id="calendar">
                    <div class="alert alert-light text-center">
                        <i class="fas fa-arrow-left me-2"></i>Vui lòng chọn một phòng từ danh sách bên trái để xem lịch.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="bookingModalLabel"><i class="fas fa-edit me-2"></i>Thông Tin Đặt Phòng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="room_id" id="selected_room_id">
                    
                    <div class="alert alert-info">
                        <strong>Phòng:</strong> <span id="display_room_name">-</span>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Thời gian bắt đầu <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="start_time" id="start_time" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Thời gian kết thúc <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="end_time" id="end_time" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Môn học / Mục đích <span class="text-danger">*</span></label>
                        <select class="form-select" name="subject_id" id="subjectSelect">
                            <option value="">--- Vui lòng chọn ---</option>
                            <?php
                            foreach ($danh_sach_mon_hoc as $mon) {
                                $ten_mon = htmlspecialchars($mon['ten_mon_hoc']);
                                echo "<option value='{$mon['id']}'>{$ten_mon} (Mã: {$mon['ma_mon_hoc']})</option>";
                            }
                            ?>
                            <option value="0">--- Khác (Vui lòng nhập bên dưới) ---</option>
                        </select>
                    </div>

                    <div class="mb-3" id="otherPurposeWrapper" style="display:none;">
                        <label class="form-label fw-bold">Mục đích khác <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="purpose" rows="2" 
                                  placeholder="VD: Họp bộ môn, Sinh hoạt CLB..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Dạy cho lớp</label>
                        <select class="form-select" name="ten_lop">
                            <option value="">--- (Không phải lịch học / Để trống) ---</option>
                            <?php
                            foreach ($danh_sach_lop as $lop) {
                                $ten_lop_option = htmlspecialchars($lop['ten_lop']);
                                echo "<option value='{$ten_lop_option}'>{$ten_lop_option}</option>";
                            }
                            ?>
                        </select>
                        <small class="text-muted">Chỉ các lớp có sinh viên trong hệ thống mới hiện ra.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-success btn-custom">
                        <i class="fas fa-paper-plane me-1"></i>Gửi Yêu Cầu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// === TOÀN BỘ SCRIPT ĐƯỢC VIẾT LẠI ===
let selectedRoomId = null;
let selectedRoomName = '';
let calendarInstance = null;
let bookingModal = null;

// Hàm helper để format ngày giờ cho input datetime-local
function formatDateTimeLocal(date) {
    // Lấy múi giờ địa phương và điều chỉnh
    const offset = date.getTimezoneOffset() * 60000;
    const localDate = new Date(date.getTime() - offset);
    // Format thành 'YYYY-MM-DDTHH:MM'
    return localDate.toISOString().slice(0, 16);
}

// Chạy khi tài liệu đã tải xong
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Khởi tạo đối tượng Modal của Bootstrap
    bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));

    // 2. Logic lọc phòng theo sức chứa
    document.getElementById('capacityFilter').addEventListener('change', function() {
        const minCapacity = parseInt(this.value) || 0;
        const rooms = document.querySelectorAll('.room-item');
        rooms.forEach(room => {
            const capacity = parseInt(room.dataset.capacity);
            if (minCapacity === 0 || capacity >= minCapacity) {
                room.style.display = 'block';
            } else {
                room.style.display = 'none';
            }
        });
    });

    // 3. Logic khi bấm chọn một phòng
    document.querySelectorAll('.room-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            // Đánh dấu active
            document.querySelectorAll('.room-item').forEach(r => r.classList.remove('active'));
            this.classList.add('active');
            
            // Lưu thông tin phòng đã chọn
            selectedRoomId = this.dataset.roomId;
            selectedRoomName = this.dataset.roomName;
            
            // Tải lịch cho phòng này
            loadRoomCalendar(selectedRoomId);
        });
    });

    // 4. Logic ẩn/hiện ô "Mục đích khác" (bên trong modal)
    document.getElementById('subjectSelect').addEventListener('change', function() {
        const otherPurposeWrapper = document.getElementById('otherPurposeWrapper');
        if (this.value === '0') {
            otherPurposeWrapper.style.display = 'block';
        } else {
            otherPurposeWrapper.style.display = 'none';
        }
    });
});

// Hàm tải dữ liệu lịch của phòng
function loadRoomCalendar(roomId) {
    fetch(`get_bookings.php?room_id=${roomId}`)
        .then(response => response.json())
        .then(bookings => {
            renderCalendar(bookings); // Vẽ lại lịch với dữ liệu mới
        });
}

// Hàm vẽ lịch
function renderCalendar(bookings) {
    const calendarEl = document.getElementById('calendar');
    
    // Nếu lịch đã tồn tại, hủy nó đi để vẽ lại
    if (calendarInstance) {
        calendarInstance.destroy();
    }
    
    // Chuyển đổi dữ liệu booking
    const events = bookings.map(booking => ({
        title: booking.purpose,
        start: booking.start_time,
        end: booking.end_time,
        backgroundColor: booking.status === 'approved' ? '#1cc88a' : '#f6c23e',
        borderColor: booking.status === 'approved' ? '#1cc88a' : '#f6c23e'
    }));
    
    // Khởi tạo FullCalendar
    calendarInstance = new FullCalendar.Calendar(calendarEl, {
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
        selectable: true, // Cho phép chọn
        
        // SỰ KIỆN QUAN TRỌNG: Khi người dùng bấm vào ô trống trên lịch
        select: function(info) {
            if (!selectedRoomId) {
                Swal.fire('Vui lòng chọn phòng!', 'Bạn cần chọn một phòng ở cột bên trái trước.', 'warning');
                return;
            }
            
            // Tự động điền thông tin vào form trong modal
            document.getElementById('selected_room_id').value = selectedRoomId;
            document.getElementById('display_room_name').textContent = selectedRoomName;
            
            // Sử dụng hàm helper để format ngày giờ chính xác
            document.getElementById('start_time').value = formatDateTimeLocal(info.start);
            document.getElementById('end_time').value = formatDateTimeLocal(info.end);
            
            // Reset form (nếu cần)
            document.getElementById('subjectSelect').value = '';
            document.getElementById('otherPurposeWrapper').style.display = 'none';
            document.querySelector("select[name='ten_lop']").value = '';
            
            // Hiển thị modal
            bookingModal.show();
        },
        
        // Sự kiện khi bấm vào lịch đã có
        eventClick: function(info) {
            Swal.fire({
                title: 'Lịch đã được đặt',
                html: `<strong>${info.event.title}</strong><br>
                       <small>${info.event.start.toLocaleString('vi-VN')} - ${info.event.end.toLocaleString('vi-VN')}</small>`,
                icon: 'info'
            });
        }
    });
    
    calendarInstance.render();
}
</script>

<?php require_once '../includes/footer.php'; ?>