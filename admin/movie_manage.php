<?php
include("../db.php");
include("../admin/dashboard.php");

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "\u274c Ch\u1ec9 qu\u1ea3n tr\u1ecb vi\u00ean m\u1edbi truy c\u1eadp \u0111\u01b0\u1ee3c.";
    exit();
}

// Thao tác xóa phim 
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM movies WHERE id = $id");
    header("Location: movie_manage.php");
    exit();
}

// lấy thông tin phim cần sửa (nếu có)
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_data = null;
if ($edit_id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM movies WHERE id = $edit_id");
    $edit_data = mysqli_fetch_assoc($res);
}

// xử lý thêm phim mới
if (isset($_POST['add'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = intval($_POST['price']);
    $is_vip = isset($_POST['is_vip']) ? 1 : 0;
    $episodes = intval($_POST['episodes']);

    $poster = '';
    $trailer = '';

    if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] === UPLOAD_ERR_OK) {
        $poster = basename($_FILES['poster_file']['name']);
        move_uploaded_file($_FILES['poster_file']['tmp_name'], "../assets/images/" . $poster);
    }

    if (isset($_FILES['trailer_file']) && $_FILES['trailer_file']['error'] === UPLOAD_ERR_OK) {
        $trailer = basename($_FILES['trailer_file']['name']);
        move_uploaded_file($_FILES['trailer_file']['tmp_name'], "../assets/trailers/" . $trailer);
    }

    mysqli_query($conn, "INSERT INTO movies (title, description, poster, trailer, price, is_vip, episodes) 
        VALUES ('$title', '$desc', '$poster', '$trailer', $price, $is_vip, $episodes)");
    header("Location: movie_manage.php");
    exit();
}

// cập nhật phim
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = intval($_POST['price']);
    $is_vip = isset($_POST['is_vip']) ? 1 : 0;
    
    if ($is_vip && $price <= 0) die("Giá phim VIP phải lớn hơn 0");
    
    $episodes = intval($_POST['episodes']);

    $poster = $_POST['poster_old'];
    $trailer = $_POST['trailer_old'];

    if (isset($_FILES['poster_file']) && $_FILES['poster_file']['error'] === UPLOAD_ERR_OK) {
        $poster = basename($_FILES['poster_file']['name']);
        move_uploaded_file($_FILES['poster_file']['tmp_name'], "../assets/images/" . $poster);
    }

    if (isset($_FILES['trailer_file']) && $_FILES['trailer_file']['error'] === UPLOAD_ERR_OK) {
        $trailer = basename($_FILES['trailer_file']['name']);
        move_uploaded_file($_FILES['trailer_file']['tmp_name'], "../assets/trailers/" . $trailer);
    }

    mysqli_query($conn, "UPDATE movies SET title='$title', description='$desc', poster='$poster', trailer='$trailer', price=$price, is_vip=$is_vip, episodes=$episodes WHERE id = $id");
    header("Location: movie_manage.php");
    exit();
}

// Danh sach phim
$movies = mysqli_query($conn, "SELECT * FROM movies ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Qu\u1ea3n l\u00fd phim</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ccc; }
        th { background-color: #eee; }
        form { margin-top: 20px; }
    </style>
</head>
<body>

<h2>🎞️ phim</h2>

<?php if ($edit_data): ?>
    <form method="post" enctype="multipart/form-data">
        <h3>✏️ Sửa phim <?= $edit_data['title'] ?></h3>
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <input type="hidden" name="poster_old" value="<?= $edit_data['poster'] ?>">
        <input type="hidden" name="trailer_old" value="<?= $edit_data['trailer'] ?>">
        <input type="text" name="title" placeholder="Tên phim" required value="<?= $edit_data['title'] ?>"><br>
        <textarea name="description" placeholder="Mô tả" required><?= $edit_data['description'] ?></textarea><br>
        Poster: <input type="file" name="poster_file" accept="image/*"><br>
        Trailer: <input type="file" name="trailer_file" accept="video/*"><br>
        <label><input type="checkbox" name="is_vip" <?= $edit_data['is_vip'] ? 'checked' : '' ?>> Phim VIP</label><br>
        <input type="number" name="price" placeholder="Giá (vnđ)" value="<?= $edit_data['price'] ?>"><br>
        <button type="submit" name="update">💾 Cập nhật</button>
    </form>
<?php else: ?>
    <form method="post" enctype="multipart/form-data">
        <h3>➕ Thêm phim mới</h3>
        <input type="text" name="title" placeholder="Tên phim" required><br>
        <textarea name="description" placeholder="Mô tả" required></textarea><br>
        Poster: <input type="file" name="poster_file" accept="image/*" required><br>
        Trailer: <input type="file" name="trailer_file" accept="video/*"><br>
        <label><input type="checkbox" name="is_vip"> Phim VIP</label><br>
        <input type="number" name="price" placeholder="Giá (vnđ)" value="0"><br>
        <button type="submit" name="add">Thêm</button>
    </form>
<?php endif; ?>

<h3>📄 Danh sách phim</h3>
<table>
    <tr>
        <th>ID</th><th>Tên</th><th>VIP</th><th>Giá</th><th>Tập</th><th>Ảnh</th><th>Trailer</th><th>Thao tác</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($movies)): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= $row['is_vip'] ? '👑 VIP' : '✔' ?></td>
            <td><?= number_format($row['price']) ?> vnđ</td>
            <td><?= $row['episodes'] ?></td>
            <td><img src="../assets/images/<?= $row['poster'] ?>" height="50"></td>
            <td>
                <?php if (!empty($row['trailer'])): ?>
                    <video src="../assets/trailers/<?= $row['trailer'] ?>" controls width="150"></video>
                <?php else: ?>
                    Không có
                <?php endif; ?>
            </td>
            <td>
                <a href="movie_manage.php?edit=<?= $row['id'] ?>">✏️ Sửa</a> |
                <a href="movie_manage.php?delete=<?= $row['id'] ?>" onclick="return confirm('Xóa phim này?')">🗑️ Xóa</a> |
                <a href="episode_manage.php?movie_id=<?= $row['id'] ?>">🎞️ Tập</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
