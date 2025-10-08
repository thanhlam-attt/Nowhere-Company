<?php
include("../db.php");
include("../main/menu.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$keyword = $_GET['keyword'] ?? '';
$keyword_safe = mysqli_real_escape_string($conn, $keyword);

$results = [];
if ($keyword !== '') {
    $sql = "SELECT * FROM movies WHERE title LIKE '%$keyword_safe%' ORDER BY created_at DESC";
    $query = mysqli_query($conn, $sql);
    $results = mysqli_fetch_all($query, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kết quả tìm kiếm</title>
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
    </style>
</head>
<body>

<h2>🔍 Kết quả tìm kiếm cho: <i>"<?= htmlspecialchars($keyword) ?>"</i></h2>

<?php if (empty($results)): ?>
    <p>❌ Không tìm thấy phim nào khớp với từ khóa.</p>
<?php else: ?>
    <?php foreach ($results as $movie): ?>
        <div class="movie">
            <a href="movie.php?id=<?= $movie['id'] ?>">
                <img src="../assets/images/<?= $movie['poster'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                <div class="movie-title"><?= htmlspecialchars($movie['title']) ?></div>
            </a>
            <?php if ($movie['is_vip']): ?>
                <div class="vip-label">Phim VIP (<?= $movie['price'] ?> vnđ)</div>
            <?php else: ?>
                <div>Miễn phí</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
