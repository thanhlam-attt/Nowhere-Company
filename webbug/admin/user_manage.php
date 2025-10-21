<?php
include("../db.php");
include("dashboard.php");

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "❌ Chỉ quản trị viên mới truy cập được.";
    exit();
}

// Xử lý đổi vai trò
if (isset($_POST['change_role'])) {
    $uid = intval($_POST['user_id']);
    $new_role = $_POST['new_role'] === 'admin' ? 'admin' : 'user';
    mysqli_query($conn, "UPDATE users SET role = '$new_role' WHERE id = $uid");
}

// Xử lý xoá người dùng
if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id = $uid");
    mysqli_query($conn, "DELETE FROM wallets WHERE user_id = $uid"); // Xoá ví nếu có
    mysqli_query($conn, "DELETE FROM purchases WHERE user_id = $uid"); // Xoá các giao dịch
    header("Location: user_manage.php");
    exit();
}

// Tìm kiếm
$keyword = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$sql = "SELECT u.*, w.balance FROM users u 
        LEFT JOIN wallets w ON u.id = w.user_id 
        WHERE u.username LIKE '%$keyword%' OR u.email LIKE '%$keyword%' 
        ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quản lý người dùng</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: center; }
        form.inline { display: inline; }
        input[type="text"] { padding: 5px; width: 250px; }
        button { padding: 5px 10px; }
        select { padding: 5px; }
    </style>
</head>
<body>

<h2>👥 Quản lý người dùng</h2>

<form method="get">
    <input type="text" name="search" placeholder="Tìm theo tên hoặc email..." value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit">🔍 Tìm</button>
</form>

<table>
    <tr>
        <th>ID</th>
        <th>Tên người dùng</th>
        <th>Email</th>
        <th>Vai trò</th>
        <th>Số dư ví</th>
        <th>Ngày tạo</th>
        <th>Thao tác</th>
    </tr>
    <?php while ($user = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
                <form method="post" class="inline">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <select name="new_role">
                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <button type="submit" name="change_role">💾</button>
                </form>
            </td>
            <td><?= number_format($user['balance'] ?? 0) ?> vnđ</td>
            <td><?= $user['created_at'] ?></td>
            <td>
                <a href="?delete=<?= $user['id'] ?>" onclick="return confirm('Bạn chắc chắn muốn xoá người dùng này?')">🗑️ Xoá</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
