<?php
include("../db.php");
include("../main/menu.php");

if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy ID phim
$movie_id = intval($_GET['id'] ?? 0);
if ($movie_id <= 0) {
    echo "❌ Không tìm thấy phim.";
    exit();
}

// Lấy thông tin phim
$result = mysqli_query($conn, "SELECT * FROM movies WHERE id = $movie_id");
$movie = mysqli_fetch_assoc($result);
if (!$movie) {
    echo "❌ Phim không tồn tại.";
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'] ?? 0;
$has_bought = false;

// Nếu là phim VIP và đã đăng nhập, kiểm tra đã mua chưa
if ($movie['is_vip'] && $user_id) {
    $check_buy = mysqli_query($conn, "SELECT id FROM purchases WHERE user_id=$user_id AND movie_id=$movie_id");
    $has_bought = mysqli_num_rows($check_buy) > 0;
}

// Xử lý mua phim nếu nhấn nút mua
if (isset($_POST['buy']) && $user_id && !$has_bought) {
    $wallet = mysqli_fetch_assoc(mysqli_query($conn, "SELECT balance FROM wallets WHERE user_id=$user_id"));
    if ($wallet && $wallet['balance'] >= $movie['price']) {
        mysqli_query($conn, "INSERT INTO purchases (user_id, movie_id) VALUES ($user_id, $movie_id)");
        mysqli_query($conn, "UPDATE wallets SET balance = balance - {$movie['price']} WHERE user_id = $user_id");
        header("Location: movie.php?id=$movie_id");
        exit();
    } else {
        echo "<p>❌ Ví không đủ tiền. <a href='cash.php'>Nạp tiền</a></p>";
    }
}

// Xử lý gửi bình luận phim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && $user_id) {
    $uid = $_SESSION['user_id'];
    $content = mysqli_real_escape_string($conn, $_POST['comment']);
    mysqli_query($conn, "INSERT INTO comments (user_id, movie_id, episode, content) VALUES ($uid, $movie_id, 0, '$content')");
    header("Location: movie.php?id=$movie_id");
    exit();
}

// Lấy bình luận chỉ cho phim (episode = 0)
$comments = mysqli_query($conn, "
    SELECT users.username, content, comments.created_at 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE movie_id = $movie_id AND (episode = 0 OR episode IS NULL)
    ORDER BY comments.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($movie['title']) ?></title>
    <style>
        .movie-container { display: flex; gap: 20px; margin-bottom: 40px; }
        .poster { width: 250px; }
        .details { max-width: 600px; }
        .episodes span {
            margin-right: 5px;
            background: #ddd;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 5px;
        }
        .vip-label { color: red; font-weight: bold; }
        .trailer { margin-top: 20px; }
        .comment-box { margin-top: 30px; }
        .comment { border-bottom: 1px dashed #999; margin-bottom: 10px; padding-bottom: 5px; }
    </style>
</head>
<body>

<h2>🎬 <?= htmlspecialchars($movie['title']) ?></h2>

<div class="movie-container">
    <div class="poster">
        <img src="../assets/images/<?= $movie['poster'] ?>" width="100%">
    </div>
    <div class="details">
        <p><b>Nội dung:</b> <?= nl2br(htmlspecialchars($movie['description'])) ?></p>

        <?php if ($movie['is_vip']): ?>
            <p class="vip-label">Phim VIP – Giá: <?= number_format($movie['price']) ?> vnđ</p>
        <?php else: ?>
            <p><b>Phim miễn phí</b></p>
        <?php endif; ?>

        <?php
            $episodes = mysqli_query($conn, "SELECT episode_number, title FROM episodes WHERE movie_id = $movie_id ORDER BY episode_number ASC");
            if (mysqli_num_rows($episodes) > 0):
            ?>
                <p><b>Số tập:</b> <?= mysqli_num_rows($episodes) ?></p>
                <div class="episodes">
                    <?php while ($ep = mysqli_fetch_assoc($episodes)): ?>
                        <?php if (!$movie['is_vip'] || $has_bought): ?>
                            <a href="watch.php?id=<?= $movie_id ?>&ep=<?= $ep['episode_number'] ?>">
                                <span>Tập <?= $ep['episode_number'] ?><?= $ep['title'] ? ": " . htmlspecialchars($ep['title']) : "" ?></span>
                            </a>
                        <?php else: ?>
                            <span style="opacity: 0.5;">Tập <?= $ep['episode_number'] ?></span>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>❌ Chưa có tập nào.</p>
            <?php endif; ?>

        <?php if (!empty($movie['trailer'])): ?>
            <div class="trailer">
                <video width="400" controls>
                    <source src="../assets/trailers/<?= $movie['trailer'] ?>" type="video/mp4">
                    Trình duyệt không hỗ trợ video.
                </video>
            </div>
        <?php endif; ?>

        <!-- Mua hoặc xác nhận đã mua -->
        <?php if ($movie['is_vip']): ?>
            <?php if (!$user_id): ?>
                <p>🔒 <a href="../main/login.php">Đăng nhập</a> để mua phim.</p>
            <?php elseif ($has_bought): ?>
                <p>✅ Bạn đã mua phim này.</p>
            <?php else: ?>
                <form method="post">
                    <button type="submit" name="buy">💰 Mua phim</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <p>✅ Phim miễn phí – bạn có thể xem các tập bên trên.</p>
        <?php endif; ?>
    </div>
</div>

<h3>💬 Bình luận phim</h3>
<div class="comment-box">
    <?php while ($cmt = mysqli_fetch_assoc($comments)): ?>
        <div class="comment">
            <b><?= htmlspecialchars($cmt['username']) ?>:</b> <?= htmlspecialchars($cmt['content']) ?><br>
            <small><?= $cmt['created_at'] ?></small>
        </div>
    <?php endwhile; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="post">
            <textarea name="comment" rows="3" cols="50" placeholder="Viết bình luận..." required></textarea><br>
            <button type="submit">Gửi bình luận</button>
        </form>
    <?php else: ?>
        <p><a href="../main/login.php">Đăng nhập</a> để bình luận phim.</p>
    <?php endif; ?>
</div>

</body>
</html>
