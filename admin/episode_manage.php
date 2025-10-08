<?php
include("../db.php");
include("../admin/dashboard.php");

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "❌ Bạn không có quyền truy cập trang này.";
    exit();
}

$movie_id = intval($_GET['movie_id'] ?? 0);

// Lấy danh sách phim
$movies = mysqli_query($conn, "SELECT id, title FROM movies ORDER BY title");

// Nếu có chọn phim
$movie = null;
if ($movie_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $movie = $stmt->get_result()->fetch_assoc();
    if (!$movie) {
        echo "❌ Không tìm thấy phim.";
        exit();
    }
}

// Thêm tập phim
if (isset($_POST['add_ep']) && $movie_id > 0) {
    $ep_num = intval($_POST['episode_number']);
    $title = trim($_POST['title']);

    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {

        // $finfo = finfo_open(FILEINFO_MIME_TYPE);
        // $mime = finfo_file($finfo, $_FILES['video_file']['tmp_name']);
        // finfo_close($finfo);

        // if ($mime !== 'video/mp4') {
        //     echo "<p style='color:red'>❌ Chỉ cho phép upload video MP4 hợp lệ. MIME nhận được: $mime</p>";
        //     exit();
        // }

        $allowed_types = ['video/mp4'];
        if (!in_array($_FILES['video_file']['type'], $allowed_types)) {
            echo "<p style='color:red'>❌ Chỉ chấp nhận video MP4.</p>";
            exit();
        }

        $folder = "../assets/episodes/$movie_id/";
        if (!file_exists($folder)) mkdir($folder, 0777, true);

        $ext = pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION);
        $filename = "ep$ep_num.$ext";
        $filepath = $folder . $filename;

        // Kiểm tra tập đã tồn tại
        $stmt = $conn->prepare("SELECT id FROM episodes WHERE movie_id = ? AND episode_number = ?");
        $stmt->bind_param("ii", $movie_id, $ep_num);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<p style='color:red'>❌ Tập số $ep_num đã tồn tại trong CSDL.</p>";
        } else {
            move_uploaded_file($_FILES['video_file']['tmp_name'], $filepath);
            $stmt = $conn->prepare("INSERT INTO episodes (movie_id, episode_number, title, file_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $movie_id, $ep_num, $title, $filename);
            $stmt->execute();
            header("Location: episode_manage.php?movie_id=$movie_id");
            exit();
        }
    }
}

// Xóa tập
if (isset($_GET['delete']) && $movie_id > 0) {
    $ep_id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT file_name FROM episodes WHERE id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $ep_id, $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ep = $result->fetch_assoc();
    if ($ep) {
        $filepath = "../assets/episodes/$movie_id/" . $ep['file_name'];
        if (file_exists($filepath)) unlink($filepath);
        $stmt = $conn->prepare("DELETE FROM episodes WHERE id = ?");
        $stmt->bind_param("i", $ep_id);
        $stmt->execute();
    }
    header("Location: episode_manage.php?movie_id=$movie_id");
    exit();
}

// Lấy danh sách tập
$episodes = [];
if ($movie_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM episodes WHERE movie_id = ? ORDER BY episode_number ASC");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $episodes[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quản lý tập phim</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .ep { margin-bottom: 30px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
        video { display: block; margin-top: 10px; }
    </style>
</head>
<body>

<h2>🎞️ Quản lý tập phim</h2>

<form method="get">
    <label>Chọn phim:</label>
    <select name="movie_id" onchange="this.form.submit()">
        <option value="">-- Chọn phim --</option>
        <?php while ($m = mysqli_fetch_assoc($movies)): ?>
            <option value="<?= $m['id'] ?>" <?= $m['id'] == $movie_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['title']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<?php if ($movie): ?>
    <h3>📁 <?= htmlspecialchars($movie['title']) ?></h3>

    <form method="post" enctype="multipart/form-data">
        <label>Số tập:</label><br>
        <input type="number" name="episode_number" required><br>

        <label>Tiêu đề tập:</label><br>
        <input type="text" name="title"><br>

        <label>Video:</label><br>
        <input type="file" name="video_file" accept="video/mp4" required><br><br>

        <button type="submit" name="add_ep">📤 Thêm tập</button>
    </form>

    <h3>📃 Danh sách tập đã có</h3>
    <?php if ($episodes): ?>
        <?php foreach ($episodes as $ep): ?>
            <div class="ep">
                <b>Tập <?= $ep['episode_number'] ?>: <?= htmlspecialchars($ep['title']) ?></b><br>
                <video src="../assets/episodes/<?= $movie_id ?>/<?= $ep['file_name'] ?>" width="300" controls></video><br>
                <a href="?movie_id=<?= $movie_id ?>&delete=<?= $ep['id'] ?>" onclick="return confirm('Xóa tập này?')">🗑️ Xóa</a>

            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>❌ Chưa có tập nào.</p>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
