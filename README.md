<h2 align="center">
    <a href="https://dainam.edu.vn/vi/khoa-cong-nghe-thong-tin">
    🎓 Faculty of Information Technology (DaiNam University)
    </a>
</h2>
<h2 align="center">
    Youth Union Member Management
</h2>
<div align="center">
    <p align="center">
        <img src="docs/logo/aiotlab_logo.png" alt="AIoTLab Logo" width="170"/>
        <img src="docs/logo/fitdnu_logo.png" alt="AIoTLab Logo" width="180"/>
        <img src="docs/logo/dnu_logo.png" alt="DaiNam University Logo" width="200"/>
    </p>

[![AIoTLab](https://img.shields.io/badge/AIoTLab-green?style=for-the-badge)](https://www.facebook.com/DNUAIoTLab)
[![Faculty of Information Technology](https://img.shields.io/badge/Faculty%20of%20Information%20Technology-blue?style=for-the-badge)](https://dainam.edu.vn/vi/khoa-cong-nghe-thong-tin)
[![DaiNam University](https://img.shields.io/badge/DaiNam%20University-orange?style=for-the-badge)](https://dainam.edu.vn)

</div>
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
### Trang quản lý phòng:
<img width="1897" height="970" alt="image" src="https://github.com/user-attachments/assets/96b44b79-2ceb-49d6-b2e6-45d1db95fcf4" />
### Trang quản lý người dùng:
<img width="1900" height="970" alt="image" src="https://github.com/user-attachments/assets/d5f7db76-edf5-4b80-98de-2a5f1f1bbe86" />
### Trang duyệt dữ liệu người dùng:
<img width="1900" height="967" alt="image" src="https://github.com/user-attachments/assets/030dea3b-5f12-4d92-84f4-f20dc0d19102" />
### Trang quản lý ngành học:
<img width="1898" height="971" alt="image" src="https://github.com/user-attachments/assets/7e5bb712-3c68-4dc5-83b6-b6269be136c5" />
### Trang quản lý môn học:
<img width="1899" height="967" alt="image" src="https://github.com/user-attachments/assets/641bdc54-a506-4d66-bdec-7b9d6fe58543" />
### Trang duyệt đặt phòng:
<img width="1898" height="972" alt="image" src="https://github.com/user-attachments/assets/aa6b8bc6-bd01-4c84-828a-9f6622a92700" />
### Trang dashboard Giảng Viên:
<img width="1897" height="971" alt="image" src="https://github.com/user-attachments/assets/20f97f7b-9019-4dc9-96c9-4f3ea43361b9" />
### Trang đặt phòng giảng viên
<img width="1899" height="971" alt="image" src="https://github.com/user-attachments/assets/dd77525e-ce0d-4ea8-89e1-f05973b0e77f" />
### Trang Tổng quan phòng
<img width="1899" height="969" alt="image" src="https://github.com/user-attachments/assets/1136b256-474a-4150-8b59-5e467f6c01de" />
### Trang lịch học Sinh Viên:
<img width="1897" height="967" alt="image" src="https://github.com/user-attachments/assets/89b4d34f-d723-4f8a-bb4a-7ee6c38cab0b" />
### Trang chủ Sinh Viên:
<img width="1898" height="969" alt="image" src="https://github.com/user-attachments/assets/dccf434b-81f8-4f8e-96ca-0018c1f365a1" />

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
https://github.com/GaminTheBasket/BTL_Quan_ly_phong_hoc.git
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



