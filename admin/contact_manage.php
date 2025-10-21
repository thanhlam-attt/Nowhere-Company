<?php
include("../db.php");
include("../admin/dashboard.php");

// Xóa liên hệ nếu có yêu cầu
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM contacts WHERE id = $id");
    header("Location: contact_manage.php");
    exit();
}

// Lấy danh sách liên hệ
$result = mysqli_query($conn, "SELECT * FROM contacts ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản lý liên hệ</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; }
        .btn-delete {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>📬 Danh sách liên hệ người dùng</h2>

<?php if (mysqli_num_rows($result) === 0): ?>
    <p>❌ Không có góp ý nào.</p>
<?php else: ?>
    <table>
        <tr>
            <th>STT</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Nội dung</th>
            <th>Thời gian gửi</th>
            <th>Thao tác</th>
        </tr>
        <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <a class="btn-delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Xác nhận xóa góp ý này?')">🗑️ Xóa</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>

</body>
</html>
