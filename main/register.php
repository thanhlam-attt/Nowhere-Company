<?php
include("../db.php");

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $role = 'user';

    // Kiểm tra trùng username hoặc email
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR (email = ? AND role = ?)");
    $stmt->bind_param("sss", $username, $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "⚠️ Tên đăng nhập đã tồn tại hoặc email đã được dùng.";
    } else {
        // Mã hóa an toàn bằng password_hash
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $role);

        if ($stmt->execute()) {
            echo "✅ Đăng ký thành công! <a href='login.php'>Đăng nhập ngay</a>";
        } else {
            echo "❌ Lỗi đăng ký: " . $stmt->error;
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head><title>Đăng ký</title></head>
<body>
    <h2>Đăng ký</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Tên đăng nhập" required><br>
        <input type="password" name="password" placeholder="Mật khẩu" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <button type="submit" name="register">Đăng ký</button>
    </form>
</body>
</html>
