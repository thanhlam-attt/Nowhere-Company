<!-- dashboard.php -->
<?php
include("../db.php");
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "❌ Trang này chỉ dành cho quản trị viên.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trang chủ Quản trị</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .card { border: 1px solid #ccc; border-radius: 8px; padding: 15px; margin: 10px 0; background: #f9f9f9; }
        .buttons a {
            display: inline-block; margin: 10px; padding: 10px 15px; border-radius: 6px;
            background-color: #007bff; color: white; text-decoration: none;
        }
        .buttons a:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<h2>👑 Xin chào, quản trị viên!</h2>

<div class="card">
    <?php
    $movie_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM movies"));
    $user_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));
    $vip_count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM movies WHERE is_vip = 1"));
    $free_count = $movie_count - $vip_count;
    $revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(price) AS total FROM movies JOIN purchases ON movies.id = purchases.movie_id"))['total'] ?? 0;
    ?>
    <p>🎞️ Số phim: <b><?= $movie_count ?></b> (VIP: <?= $vip_count ?> / Miễn phí: <?= $free_count ?>)</p>
    <p>👥 Người dùng: <b><?= $user_count ?></b></p>
    <p>💰 Doanh thu: <b><?= number_format($revenue) ?> vnđ</b></p>
</div>

<div class="buttons">
    <a href="movie_manage.php">🎞️ Quản lý phim</a>
    <a href="user_manage.php">👤 Quản lý người dùng</a>
    <a href="transaction_log.php">💰 Giao dịch & Ví</a>
    <a href="comment_manage.php">💬 Quản lý bình luận</a>
    <a href="contact_manage.php">💬 Quản lý góp ý</a>
    <a href="../pages/index.php">Thoát trang quản trị</a>
    <a href="../main/logout.php">🔒 Đăng xuất</a>
</div>

</body>
</html>
