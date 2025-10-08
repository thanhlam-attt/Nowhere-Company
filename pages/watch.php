<?php
include("../db.php");
include("../main/menu.php");

if (session_status() === PHP_SESSION_NONE) session_start();

$movie_id = intval($_GET['id'] ?? 0);
$ep = intval($_GET['ep'] ?? 1);

if ($movie_id <= 0 || $ep <= 0) {
    echo "❌ Không xác định được phim hoặc tập.";
    exit();
}

// Lấy thông tin phim
$result = mysqli_query($conn, "SELECT * FROM movies WHERE id = $movie_id");
$movie = mysqli_fetch_assoc($result);
if (!$movie) {
    echo "❌ Phim không tồn tại.";
    exit();
}

// Kiểm tra quyền xem
$user_id = $_SESSION['user_id'] ?? 0;
$has_bought = false;

if ($movie['is_vip']) {
    if (!$user_id) {
        echo "🔒 <a href='../main/login.php'>Đăng nhập</a> để xem phim VIP.";
        exit();
    }

    $check = mysqli_query($conn, "SELECT id FROM purchases WHERE user_id = $user_id AND movie_id = $movie_id");
    $has_bought = mysqli_num_rows($check) > 0;
    if (!$has_bought) {
        echo "❌ Bạn chưa mua phim này. <a href='movie.php?id=$movie_id'>Quay lại</a>";
        exit();
    }
}

// Đường dẫn tập phim
$episode_path = "../assets/episodes/$movie_id/ep$ep.mp4";
if (!file_exists($episode_path)) {
    echo "❌ Tập $ep chưa có video.";
    exit();
}

// Xử lý gửi bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && $user_id) {
    $content = mysqli_real_escape_string($conn, $_POST['comment']);
    mysqli_query($conn, "INSERT INTO comments (user_id, movie_id, episode, content) VALUES ($user_id, $movie_id, $ep, '$content')");
    header("Location: watch.php?id=$movie_id&ep=$ep");
    exit();
}

// Lấy bình luận của tập hiện tại
$comments = mysqli_query($conn, "
    SELECT users.username, comments.content, comments.created_at 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE movie_id = $movie_id AND episode = $ep 
    ORDER BY comments.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Xem phim - <?= htmlspecialchars($movie['title']) ?> - Tập <?= $ep ?></title>
    <style>
        body { text-align: center; font-family: Arial; }
        video { margin-top: 20px; width: 80%; max-width: 800px; border: 2px solid #333; }
        .comment-box { max-width: 800px; margin: 30px auto; text-align: left; }
        .comment { border-bottom: 1px dashed #999; margin-bottom: 10px; padding-bottom: 5px; }
    </style>
</head>
<body>

<h2>🎬 <?= htmlspecialchars($movie['title']) ?> - Tập <?= $ep ?></h2>

<video controls>
    <source src="<?= $episode_path ?>" type="video/mp4">
    Trình duyệt không hỗ trợ video.
</video>

<p><a href="movie.php?id=<?= $movie_id ?>">⬅ Quay lại thông tin phim</a></p>

<h3>💬 Bình luận tập <?= $ep ?></h3>
<div class="comment-box">
    <?php while ($cmt = mysqli_fetch_assoc($comments)): ?>
        <div class="comment">
            <b><?= htmlspecialchars($cmt['username']) ?>:</b> <?= htmlspecialchars($cmt['content']) ?><br>
            <small><?= $cmt['created_at'] ?></small>
        </div>
    <?php endwhile; ?>

    <?php if ($user_id): ?>
        <form method="post">
            <textarea name="comment" rows="3" cols="60" placeholder="Viết bình luận..." required></textarea><br>
            <button type="submit">Gửi bình luận</button>
        </form>
    <?php else: ?>
        <p><a href="../main/login.php">Đăng nhập</a> để bình luận.</p>
    <?php endif; ?>
</div>

</body>
</html>
