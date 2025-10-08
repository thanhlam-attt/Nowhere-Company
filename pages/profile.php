<?php
include("../db.php");
include("../main/menu.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../main/login.php");
    exit();
}

$logged_user_id = $_SESSION['user_id'];
$user_id = isset($_GET['id']) ? intval($_GET['id']) : $logged_user_id;

$logged_user_id = $_SESSION['user_id'];
$requested_id = isset($_GET['id']) ? intval($_GET['id']) : $logged_user_id;

// Nếu không phải admin, chỉ được xem hồ sơ của chính mình
if ($_SESSION['role'] != 'admin' && $requested_id != $logged_user_id) {
    echo "❌ Không có quyền truy cập hồ sơ người khác.";
    exit();
}


// Đổi tên
if (isset($_POST['update_name'])) {
    $new_name = trim($_POST['new_name']);
    if ($new_name !== '') {
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $user_id);
        $stmt->execute();
        if ($user_id === $logged_user_id) {
            $_SESSION['username'] = $new_name;
        }
        header("Location: profile.php" . ($user_id !== $logged_user_id ? "?id=$user_id" : ""));
        exit();
    } else {
        echo "<p style='color:red;'>⚠️ Tên không được để trống.</p>";
    }
}

// Đổi mật khẩu
if (isset($_POST['update_password'])) {
    $current = $_POST['current_password'];
    $newpass = $_POST['new_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data && $data['password'] === md5($current)) {
        if (strlen($newpass) >= 6) {
            $new_hashed = md5($newpass); // Có thể nâng cấp lên password_hash()
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_hashed, $user_id);
            $stmt->execute();
            echo "<p style='color:green;'>✅ Đã cập nhật mật khẩu mới.</p>";
        } else {
            echo "<p style='color:red;'>⚠️ Mật khẩu mới phải từ 6 ký tự trở lên.</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ Mật khẩu hiện tại không đúng.</p>";
    }
}

// Lấy thông tin người dùng
$stmt = $conn->prepare("SELECT username, role, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo "Không tìm thấy người dùng.";
    exit();
}
$username = $row['username'];
$role = $row['role'];
$balance = $row['balance'] ?? 0;

// Lịch sử nạp tiền
$stmt = $conn->prepare("SELECT amount, created_at FROM topups WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$topups = $stmt->get_result();

// Lịch sử mua phim
$stmt = $conn->prepare("
    SELECT movies.title, purchases.purchase_time 
    FROM purchases 
    JOIN movies ON purchases.movie_id = movies.id 
    WHERE purchases.user_id = ? 
    ORDER BY purchases.purchase_time DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$purchases = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hồ sơ cá nhân</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .section { margin-bottom: 40px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ccc; }
        th { background-color: #eee; }
        input[type="text"], input[type="password"] { padding: 6px; width: 250px; }
        button { padding: 6px 12px; }
    </style>
</head>
<body>

<h2>👤 Hồ sơ người dùng</h2>

<div class="section">
    <form method="post">
        <p><b>Tên đăng nhập:</b> 
            <input type="text" name="new_name" value="<?= htmlspecialchars($username) ?>">
            <button type="submit" name="update_name">✏️ Đổi tên</button>
        </p>
    </form>
    <p><b>Vai trò:</b> <?= htmlspecialchars($role) ?></p>
    <p><b>Số dư hiện tại:</b> <?= number_format($balance) ?> vnđ</p>
</div>

<div class="section">
    <h3>🔐 Đổi mật khẩu</h3>
    <form method="post">
        <p><input type="password" name="current_password" placeholder="Mật khẩu hiện tại" required></p>
        <p><input type="password" name="new_password" placeholder="Mật khẩu mới" required></p>
        <button type="submit" name="update_password">Cập nhật mật khẩu</button>
    </form>
</div>

<div class="section">
    <h3>💳 Lịch sử nạp tiền</h3>
    <table>
        <tr><th>Số tiền</th><th>Thời gian</th></tr>
        <?php while ($t = mysqli_fetch_assoc($topups)): ?>
            <tr>
                <td><?= number_format($t['amount']) ?> vnđ</td>
                <td><?= $t['created_at'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="section">
    <h3>🎬 Lịch sử mua phim VIP</h3>
    <table>
        <tr><th>Tên phim</th><th>Thời gian mua</th></tr>
        <?php while ($p = mysqli_fetch_assoc($purchases)): ?>
            <tr>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= $p['purchase_time'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
