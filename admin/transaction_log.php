<?php
include("../db.php");
include("../admin/dashboard.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chỉ cho phép admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "⚠️ Chỉ quản trị viên mới có quyền truy cập.";
    exit();
}

// Lấy lịch sử nạp tiền
$topups = mysqli_query($conn, "
    SELECT users.username, topups.amount, topups.created_at 
    FROM topups 
    JOIN users ON topups.user_id = users.id
    ORDER BY topups.created_at DESC
");


// Lấy lịch sử mua phim
$purchases = mysqli_query($conn, "
    SELECT users.username, movies.title, purchases.purchase_time 
    FROM purchases 
    JOIN users ON purchases.user_id = users.id 
    JOIN movies ON purchases.movie_id = movies.id 
    ORDER BY purchase_time DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>📊 Lịch sử giao dịch</title>
    <style>
        table { border-collapse: collapse; margin-bottom: 40px; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>📥 Lịch sử nạp tiền</h2>
    <table>
        <tr><th>Người dùng</th><th>Số tiền</th><th>Thời gian</th></tr>
        <?php while ($t = mysqli_fetch_assoc($topups)): ?>
            <tr>
                <td><?= htmlspecialchars($t['username']) ?></td>
                <td><?= number_format($t['amount']) ?> vnđ</td>
                <td><?= $t['created_at'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>🎟️ Lịch sử mua phim VIP</h2>
    <table>
        <tr><th>Người dùng</th><th>Tên phim</th><th>Thời gian</th></tr>
        <?php while ($p = mysqli_fetch_assoc($purchases)): ?>
            <tr>
                <td><?= htmlspecialchars($p['username']) ?></td>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= $p['purchase_time'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
