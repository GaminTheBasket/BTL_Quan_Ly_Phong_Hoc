<?php
session_start();
require_once 'includes/db_connect.php'; // Nạp file kết nối CSDL

// Kiểm tra xem đã đăng nhập chưa, và không phải là admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'admin') {
    header("Location: index.php"); // Nếu là admin hoặc chưa đăng nhập, đá về trang login
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trang chủ - Quản lý Phòng học</title>
  
  <link rel="stylesheet" href="css/dashboard.css"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <header class="app-header">
    <div class="container nav-bar">
      <button id="openSidebar" class="hamburger" aria-label="Mở menu">☰</button>
      <div class="brand">
        <div class="brand-icon">📘</div>
        <div class="brand-name">SmartClassroom</div>
      </div>
      <div class="header-right">
        <div style="color: var(--ink); font-weight: 600; margin-right: 15px;">
          Chào, <?php echo htmlspecialchars($full_name); ?>!
        </div>
        <nav class="nav minimal">
          <a class="btn pill login-btn" href="logout.php">Đăng xuất</a>
        </nav>
      </div>
    </div>
  </header>

  <div id="overlay" class="overlay" tabindex="-1"></div>
  
  <aside id="sidebar" class="side-drawer">
    <div class="side-header">
      <div class="brand">
        <div class="brand-icon">📘</div>
        <div class="brand-name">SmartClassroom</div>
      </div>
      <button id="closeSidebar" class="close" aria-label="Đóng menu">×</button>
    </div>
    <nav class="side-nav">
      <a href="home.php" style="font-weight: bold; background-color: #f1f5f9;">
        <i class="fas fa-home me-2"></i> Trang chủ
      </a>
      
      <?php if ($role == 'teacher'): ?>
        <a href="teacher/index.php">
            <i class="fas fa-arrow-right-to-bracket me-2"></i> Vào Dashboard
        </a>
      <?php endif; ?>
      
      <?php if ($role == 'student'): ?>
        <a href="student/index.php">
            <i class="fas fa-arrow-right-to-bracket me-2"></i> Vào trang Tra cứu
        </a>
      <?php endif; ?>
      
      <a href="logout.php">
        <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
      </a>
    </nav>
  </aside>
  <main>
    <section class="hero" id="home">
      <div class="video-background">
        <video autoplay muted loop playsinline>
          <source src="video/dashboard_video.mp4" type="video/mp4">
        </video>
      </div>
      <div class="container hero-grid">
        <div class="hero-copy nudge-left">
          <div class="badge"><span>✨</span> Chào mừng trở lại!</div>
          <h1 class="display quote-lines" id="quoteLines">
            <span>Quản lý Thông minh.</span>
            <span class="gradient">Học tập Thông minh hơn.</span>
          </h1>
          <p id="quoteSub">Quản lý phòng học, lịch và việc sử dụng tại một nơi.</p>
          
          <div class="hero-cta">
            <?php if ($role == 'teacher'): ?>
              <a class="btn primary pill" href="teacher/index.php">Vào Dashboard</a>
              <a class="btn ghost pill" href="teacher/booking_new.php">Đặt Phòng Mới</a>
            <?php elseif ($role == 'student'): ?>
              <a class="btn primary pill" href="student/index.php">Tra Cứu Lịch Học</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="section muted content-section">
      <div class="container">
        <h2 class="section-title center">Tính năng nổi bật</h2>
        <div class="features-grid">
          <article class="feature feature-hover">
            <div class="feature-icon">🗓️</div>
            <div class="feature-text"><h3>Lập lịch thông minh</h3><p>Tạo thời khóa biểu tự động giải quyết xung đột.</p></div>
          </article>
          <article class="feature feature-hover">
            <div class="feature-icon">📡</div>
            <div class="feature-text"><h3>Trạng thái thời gian thực</h3><p>Theo dõi việc sử dụng phòng ngay lập tức.</p></div>
          </article>
          <article class="feature feature-hover">
            <div class="feature-icon">🛠️</div>
            <div class="feature-text"><h3>Quản lý thiết bị</h3><p>Quản lý máy chiếu, âm thanh với nhắc nhở bảo trì.</p></div>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer-rows">
    <div class="container f-copy">© 2025 SmartCampus University — Bảo lưu mọi quyền.</div>
  </footer>
</body>
<script src="js/app.js"></script>
<script src="js/hero.js"></script>
<script src="js/home-scroll.js"></script>
</html>