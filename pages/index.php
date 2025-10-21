<?php
include("../db.php");
include("../main/menu.php");

// Xử lý gửi bình luận tổng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['forum_comment'])) {
    $uid = $_SESSION['user_id'] ?? 0;
    $comment = $_POST['forum_comment'];
    mysqli_query($conn, "INSERT INTO comments (user_id, movie_id, content) VALUES ($uid, NULL, '$comment')");
}

// Lấy phim
$movies = mysqli_query($conn, "SELECT * FROM movies ORDER BY created_at DESC");

// Lấy bình luận chung (movie_id NULL)
$forum_comments = mysqli_query($conn, "
    SELECT users.username, content, comments.created_at 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE movie_id IS NULL 
    ORDER BY comments.created_at DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trang chủ - Xem phim</title>
    <style>
        .movie {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 15px;
            width: 200px;
            display: inline-block;
            vertical-align: top;
            text-align: center;
        }
        .movie img {
            width: 100%;
        }
        .vip-label { color: red; font-weight: bold; }
        .forum-box {
            margin-top: 50px;
            border-top: 2px solid #666;
            padding-top: 20px;
        }
        .comment {
            border-bottom: 1px dashed #999;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>

<h2>Danh sách phim mới</h2>
<?php while ($row = mysqli_fetch_assoc($movies)): ?>
    <div class="movie">
        <a href="movie.php?id=<?= $row['id'] ?>">
            <img src="../assets/images/<?= $row['poster'] ?>" alt="<?= htmlspecialchars($row['title']) ?>">
            <div class="movie-title"><?= htmlspecialchars($row['title']) ?></div>
        </a>
        <?php if ($row['is_vip']): ?>
            <div class="vip-label">Phim VIP (<?= $row['price'] ?> vnđ)</div>
        <?php else: ?>
            <div>Miễn phí</div>
        <?php endif; ?>
    </div>
<?php endwhile; ?>

<div class="forum-box">
    <h3>💬 Bình luận tổng / Gợi ý phim</h3>

    <?php while ($cmt = mysqli_fetch_assoc($forum_comments)): ?>
        <div class="comment">
            <b><?= htmlspecialchars($cmt['username']) ?>:</b>
            <?= htmlspecialchars($cmt['content']) ?> <br>
            <small><?= $cmt['created_at'] ?></small>
        </div>
    <?php endwhile; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="post">
            <textarea name="forum_comment" rows="3" cols="50" placeholder="Bình luận chung về phim, góp ý..." required></textarea><br>
            <button type="submit">Gửi bình luận</button>
        </form>
    <?php else: ?>
        <p>🔒 <a href="../main/login.php">Đăng nhập</a> để bình luận tổng</p>
    <?php endif; ?>
</div>

</body>
</html>
