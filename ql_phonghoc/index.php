<?php
session_start();

// 1. LOGIC PHP: Nếu đã đăng nhập, chuyển hướng ngay
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
    } elseif ($_SESSION['role'] == 'teacher') {
        header("Location: teacher/index.php");
    } else {
        header("Location: student/index.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập - Quản lý Phòng học</title>
  <meta name="description" content="Hệ thống Quản lý Phòng học">
  
  <link rel="stylesheet" href="css/style.css">
  
  <style>
    .login-alert {
      padding: 1rem;
      margin-bottom: 1rem;
      border: 1px solid transparent;
      border-radius: 0.25rem;
      font-weight: 600;
      text-align: left; /* Căn lề trái cho text lỗi */
    }
    .login-alert.error-alert {
      color: #721c24;
      background-color: #f8d7da;
      border-color: #f5c6cb;
    }
  </style>
</head>
<body class="login-body">

  <div class="video-background-login">
    <video autoplay muted loop playsinline>
      <source src="video/backgr.mp4" type="video/mp4">
    </video>
  </div>

  <main class="login-grid">
    
    <div class="login-card">
      
      <h1 class="login-title">Đăng nhập</h1>
      <p class="login-sub">Sử dụng tài khoản hệ thống của bạn</p>
      
      <?php
      if (isset($_SESSION['error'])) {
          echo '<div class="login-alert error-alert">' . htmlspecialchars($_SESSION['error']) . '</div>';
          unset($_SESSION['error']);
      }
      ?>

      <form class="login-ui" action="login_process.php" method="POST">
        <div class="field">
          <div class="input-group">
            <span class="input-icon" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </span>
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
          </div>
        </div>
        <div class="field">
          <div class="input-group">
            <span class="input-icon" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </span>
            <input type="password" name="password" placeholder="Mật khẩu" required id="passwordInput">
            
            <button type="button" class="password-toggle" id="togglePassword" aria-label="Hiện mật khẩu">
              <svg class="icon-eye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              <svg class="icon-eye-off" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" x2="22" y1="2" y2="22"></line></svg>
            </button>
            </div>
        </div>

        <div class="login-row" style="display: none;">
          <label class="remember"><input type="checkbox"> Ghi nhớ đăng nhập</label>
          <a class="forgot" href="#">Quên mật khẩu?</a>
        </div>
        
        <button class="btn primary login-btn" type="submit">Đăng nhập</button>
      </form>

      <div class="social-login" style="display: none;">
        <p class="social-text">hoặc đăng nhập với</p>
        <div class="social-icons">...</div>
      </div>

      <div class="card-footer" style="display: none;">
        <p class="register-text">Chưa có tài khoản? <a href="#" class="register-link">Đăng ký ngay</a></p>
        <a class="back" href="#">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          Quay về trang chủ
        </a>
      </div>

    </div>
  </main>

  <script src="js/login.js"></script>
</body>
</html>