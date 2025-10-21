<?php
include("../db.php");
session_start();

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    // $stmt->bind_param("s", $username);
    // $stmt->execute();
    // $result = $stmt->get_result();
    $username = addslashes(trim($_POST['username']));
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);



    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $dbHash = $user['password'];

        // Kiểm tra nếu dùng password_hash
        if (password_verify($password, $dbHash)) {
            // Mật khẩu mới – cho đăng nhập
        } 
        // Kiểm tra nếu là MD5 cũ
        else if ($dbHash === md5($password)) {
            // Mật khẩu cũ đúng → nâng cấp sang password_hash
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->bind_param("si", $newHash, $user['id']);
            $updateStmt->execute();
        } 
        else {
            $error = "⚠️ Sai mật khẩu.";
        }

        if (!isset($error)) {
            // Lưu session và chuyển trang
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: ../pages/index.php");
            exit();
        }
    } else {
        $error = "⚠️ Không tìm thấy người dùng.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Đăng nhập</title></head>
<body>
    <h2>Đăng nhập</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Tên đăng nhập" required><br>
        <input type="password" name="password" placeholder="Mật khẩu" required><br>
        <button type="submit" name="login">Đăng nhập</button>
        <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </form>
</body>
</html>


