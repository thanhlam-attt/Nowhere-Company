<?php
include("../db.php");
include("../main/menu.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Xử lý gửi bình luận
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['forum_comment'])) {
//     $uid = $_SESSION['user_id'] ?? 0;
//     $comment = trim($_POST['forum_comment']);

//     if ($uid && $comment) {
//         $comment = mysqli_real_escape_string($conn, $comment);
//         mysqli_query($conn, "INSERT INTO comments (user_id, movie_id, content) VALUES ($uid, NULL, '$comment')");
//         header("Location: forum.php"); // Refresh để tránh gửi lại
//         exit();
//     }
// }
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['forum_comment'])) {
    $uid = $_SESSION['user_id'] ?? 0;
    $comment = trim($_POST['forum_comment']);

    if ($uid && $comment) {
        // KHÔNG escape, KHÔNG nối chuỗi. Dùng prepared statement để tránh lỗi cú pháp
        $stmt = $conn->prepare("INSERT INTO comments (user_id, movie_id, content) VALUES (?, NULL, ?)");
        $stmt->bind_param("is", $uid, $comment);
        $stmt->execute();

        header("Location: forum.php"); // tránh gửi lại form khi reload
        exit();
    }
}

// Lấy tất cả bình luận chung (movie_id NULL)
$forum_comments = mysqli_query($conn, "
    SELECT users.username, content, comments.created_at 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE movie_id IS NULL 
    ORDER BY comments.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>💬 Diễn đàn gợi ý phim</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .comment-box {
            border-bottom: 1px solid #ccc;
            padding: 10px 0;
        }
        .comment-box b {
            color: #006;
        }
        .comment-box small {
            color: #999;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }
        .form-box {
            margin-top: 30px;
        }
    </style>
</head>
<body>

<h2>📢 Gợi ý phim / Bình luận tổng quát</h2>

<?php while ($cmt = mysqli_fetch_assoc($forum_comments)): ?>
    <div class="comment-box">
        <b><?= htmlspecialchars($cmt['username']) ?>:</b><br>
        <!-- <?= nl2br(htmlspecialchars($cmt['content'])) ?><br> -->

        <?= nl2br($cmt['content']) ?>

        <small>🕒 <?= $cmt['created_at'] ?></small>
    </div>
<?php endwhile; ?>

<div class="form-box">
    <h3>Viết bình luận</h3>
    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="post">
            <textarea name="forum_comment" rows="4" placeholder="Gợi ý phim hay, góp ý, thảo luận chung..." required></textarea><br>
            <button type="submit">Gửi bình luận</button>
        </form>
    <?php else: ?>
        <p>🔒 Vui lòng <a href="../main/login.php">đăng nhập</a> để viết bình luận.</p>
    <?php endif; ?>
</div>

</body>
</html>
