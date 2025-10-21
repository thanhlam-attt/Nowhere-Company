<?php
include("../db.php");
include("../main/menu.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);
    if ($stmt->execute()) {
        echo "<script>alert('✅ Gửi góp ý thành công!');</script>";
    } else {
        echo "<script>alert('❌ Lỗi khi gửi.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Liên hệ</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .contact-container { max-width: 600px; margin: auto; }
        .contact-container h2 { margin-bottom: 20px; }
        .contact-info { margin-top: 20px; }
        .contact-info p { margin: 8px 0; }
    </style>
</head>
<body>

<div class="contact-container">
    <h2>📞 Liên hệ chúng tôi</h2>

    <form method="post" action="#">
        <label>Họ và tên:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Nội dung:</label><br>
        <textarea name="message" rows="5" required></textarea><br><br>

        <button type="submit">📨 Gửi góp ý</button>
    </form>

    <div class="contact-info">
        <h3>🌐 Thông tin khác</h3>
        <p><b>Email:</b> support@xemphim.vn</p>
        <p><b>Hotline:</b> 0909 123 456</p>
        <p><b>Địa chỉ:</b> 123 Đường Phim, TP.HCM</p>
    </div>
</div>

</body>
</html>
