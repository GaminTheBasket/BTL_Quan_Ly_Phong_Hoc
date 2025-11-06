1. Giới thiệu
Hệ thống Quản lý Đoàn viên trong trường Đại học được xây dựng nhằm hỗ trợ công tác quản lý, theo dõi và đánh giá hoạt động của Đoàn Thanh niên trong môi trường giáo dục đại học. Thay vì quản lý thủ công bằng giấy tờ hay các tệp Excel rời rạc, hệ thống mang đến một giải pháp tập trung, hiện đại và dễ sử dụng.
2. Các công nghệ được sử dụng
- Backend: PHP 
- Frontend: HTML5, CSS3, JavaScript
- Framework CSS: Bootstrap 
- Database: MySQL 
Thư viện JS:
- jQuery 
- FullCalendar 
- SweetAlert2 
- Font Awesome
3. Hình ảnh các chức năng:
  <img width="1881" height="969" alt="image" src="https://github.com/user-attachments/assets/94f616c6-1c5e-423b-9e7c-0ecfc639e741" />
### Trang dashboard admin:
<img width="1880" height="967" alt="image" src="https://github.com/user-attachments/assets/8b58a464-8dae-4551-b789-e8d0a440cd0f" />
### Trang dashboard Giảng Viên:
<img width="1897" height="971" alt="image" src="https://github.com/user-attachments/assets/20f97f7b-9019-4dc9-96c9-4f3ea43361b9" />
### Trang lịch học Sinh Viên:
<img width="1897" height="967" alt="image" src="https://github.com/user-attachments/assets/89b4d34f-d723-4f8a-bb4a-7ee6c38cab0b" />
4. Cài đặt
4.1. Cài đặt công cụ, môi trường và các thư viện cần thiết
Tải và cài đặt XAMPP
👉 https://www.apachefriends.org/download.html
(Khuyến nghị bản XAMPP với PHP 8.x)

Cài đặt Visual Studio Code và các extension:

PHP Intelephense
MySQL
Prettier – Code Formatter
4.2. Tải project
Clone project về thư mục htdocs của XAMPP (ví dụ ổ C):

cd C:\xampp\htdocs
https://github.com/tyanzuq2811/BTL_Quan_ly_phong_hoc.git
Truy cập project qua đường dẫn:
👉 [http://localhost/authentication_login.](http://localhost/ql_phonghoc/index.php)
4.3. Setup database
Mở XAMPP Control Panel, Start Apache và MySQL

Truy cập MySQL WorkBench Tạo database:

CREATE DATABASE IF NOT EXISTS ql_phonghoc
   CHARACTER SET utf8mb4
   COLLATE utf8mb4_unicode_ci;
4.4. Setup tham số kết nối
Mở file config.php (hoặc .env) trong project, chỉnh thông tin DB:

<?php
    function getDbConnection() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "ql_phonghoc";
        $port = 3306;
        $conn = mysqli_connect($servername, $username, $password, $dbname, $port);
        if (!$conn) {
            die("Kết nối database thất bại: " . mysqli_connect_error());
        }
        mysqli_set_charset($conn, "utf8");
        return $conn;
    }
?>
4.5. Chạy hệ thống
Mở XAMPP Control Panel → Start Apache và MySQL

Truy cập hệ thống: 👉 http://localhost/index.php

4.6. Đăng nhập lần đầu
Hệ thống có thể cấp tài khoản admin

Sau khi đăng nhập Admin có thể:

-  Tạo thông tin và cấp tài khoản người dùng (sinh viên , giảng viên)
-  Duyệt và từ chối đặt phòng 
-  Quản lý phòng 
-  Quản lý sinh viên/giảng viên 
-  Quản lý ngành học 
-  Quản lý môn học



