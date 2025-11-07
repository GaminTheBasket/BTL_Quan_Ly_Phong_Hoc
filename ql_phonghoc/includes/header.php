<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /ql_phonghoc/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Quản lý Phòng học</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
    
    <link rel="stylesheet" href="/ql_phonghoc/css/dashboard.css">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --sidebar-width: 260px;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fc;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        .sidebar-brand-text {
            color: #fff;
            font-size: 1.2rem;
            font-weight: 700;
            text-decoration: none;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-left: 4px solid #fff;
        }
        .sidebar-menu li a i {
            width: 30px;
            font-size: 1.1rem;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        .topbar {
            background-color: #fff;
            padding: 1rem 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
        }
        .content-wrapper {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <a href="/ql_phonghoc/home.php" class="sidebar-brand-text">
                <i class="fas fa-school"></i> Quản Lý Phòng Học
            </a>
        </div>
        <ul class="sidebar-menu">
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <li><a href="/ql_phonghoc/admin/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a></li>
                
                <?php
                $room_pages = ['rooms.php', 'room_add.php', 'room_edit.php'];
                $is_room_active = in_array(basename($_SERVER['PHP_SELF']), $room_pages);
                ?>
                <li><a href="/ql_phonghoc/admin/rooms.php" class="<?php echo $is_room_active ? 'active' : ''; ?>">
                    <i class="fas fa-door-open"></i> <span>Quản lý Phòng</span>
                </a></li>
                <li><a href="/ql_phonghoc/admin/users.php" class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['users.php', 'user_add.php', 'user_edit.php'])) ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> <span>Quản lý Người dùng</span>
                </a></li>
                <li><a href="/ql_phonghoc/admin/user_import.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'user_import.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-import"></i> <span>Nhập hàng loạt (CSV)</span>
                </a></li>
                <li><a href="/ql_phonghoc/admin/majors.php" class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['majors.php', 'major_add.php', 'major_edit.php'])) ? 'active' : ''; ?>">
                    <i class="fas fa-university"></i> <span>Quản lý Ngành học</span>
                </a></li>
                <li><a href="/ql_phonghoc/admin/subjects.php" class="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['subjects.php', 'subject_add.php', 'subject_edit.php'])) ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i> <span>Quản lý Môn học</span>
                </a></li>
                <li><a href="/ql_phonghoc/admin/bookings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> <span>Duyệt Đặt phòng</span>
                </a></li>
            
            <?php elseif ($_SESSION['role'] == 'teacher'): ?>
                <li><a href="/ql_phonghoc/home.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> <span>Trang chủ</span>
                </a></li>
                <li><a href="/ql_phonghoc/teacher/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i> <span>Lịch sử đặt</span>
                </a></li>
                
                <li><a href="/ql_phonghoc/teacher/master_calendar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'master_calendar.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i> <span>Lịch Tổng Quan</span>
                </a></li>
                
                <li><a href="/ql_phonghoc/teacher/booking_new.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'booking_new.php' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i> <span>Đặt Phòng Mới</span>
                </a></li>
                
            <?php elseif ($_SESSION['role'] == 'student'): ?>
                <li><a href="/ql_phonghoc/home.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> <span>Trang chủ</span>
                </a></li>
                <li><a href="/ql_phonghoc/student/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-search"></i> <span>Tra Cứu Lịch</span>
                </a></li>
            <?php endif; ?>
            
            <li><a href="/ql_phonghoc/logout.php">
                <i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span>
            </a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="topbar">
            <h4 class="mb-0"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h4>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                </div>
                <div>
                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    <small class="text-muted">
                        <?php 
                        $roles = ['admin' => 'Quản trị viên', 'teacher' => 'Giảng viên', 'student' => 'Sinh viên'];
                        echo $roles[$_SESSION['role']];
                        ?>
                    </small>
                </div>
            </div>
        </div>
        <div class="content-wrapper">