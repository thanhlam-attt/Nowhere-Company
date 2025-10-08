<?php
include("../db.php");
include("../admin/dashboard.php");

// Xử lý xóa bình luận
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM comments WHERE id = $id");
    header("Location: comment_manage.php");
    exit();
}

// Danh sách phim để chọn lọc
$movies_list = mysqli_query($conn, "SELECT id, title FROM movies ORDER BY title ASC");

// Lấy từ khóa và phim lọc
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$movie_id = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0;

// Truy vấn có lọc
$sql = "
    SELECT comments.id, users.username, comments.content, comments.created_at, 
           movies.title AS movie_title
    FROM comments
    JOIN users ON comments.user_id = users.id
    LEFT JOIN movies ON comments.movie_id = movies.id
    WHERE 1
";

if ($keyword !== '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $sql .= " AND comments.content LIKE '%$kw%'";
}
if ($movie_id > 0) {
    $sql .= " AND comments.movie_id = $movie_id";
}

$sql .= " ORDER BY comments.created_at DESC";
$comments = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quản lý bình luận</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; }
        th { background-color: #eee; }
        .content { max-width: 400px; word-wrap: break-word; }
    </style>
</head>
<body>

<h2>💬 Quản lý bình luận</h2>

<!-- Form lọc bình luận -->
<form method="get" style="margin-top: 10px;">
    <input type="text" name="keyword" placeholder="Tìm từ khóa nội dung..." 
           value="<?= htmlspecialchars($keyword) ?>"
           style="padding: 5px; width: 250px; border-radius: 4px;">
    
    <select name="movie_id" style="padding: 5px; border-radius: 4px;">
        <option value="0">-- Tất cả phim --</option>
        <?php while ($mv = mysqli_fetch_assoc($movies_list)): ?>
            <option value="<?= $mv['id'] ?>" <?= $movie_id == $mv['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($mv['title']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit" style="padding: 5px;">🔍 Lọc</button>
    <?php if ($keyword || $movie_id): ?>
        <a href="comment_manage.php" style="margin-left:10px;">🔄 Xóa bộ lọc</a>
    <?php endif; ?>
</form>

<!-- Bảng bình luận -->
<table>
    <tr>
        <th>ID</th>
        <th>Người dùng</th>
        <th>Phim</th>
        <th>Nội dung</th>
        <th>Thời gian</th>
        <th>Thao tác</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($comments)): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= $row['movie_title'] ? htmlspecialchars($row['movie_title']) : '<i>Bình luận tổng</i>' ?></td>
            <td class="content"><?= htmlspecialchars($row['content']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <a href="comment_manage.php?delete=<?= $row['id'] ?>" 
                   onclick="return confirm('Xóa bình luận này?')">🗑️ Xóa</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
