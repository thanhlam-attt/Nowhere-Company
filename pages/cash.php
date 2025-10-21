<?php
include("../db.php");
include("../main/menu.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu chưa đăng nhập, chuyển về trang login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../main/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Xử lý nạp tiền
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $amount = intval($_POST['amount']);
    if ($amount > 0) {
        // Cộng vào bảng users
        $stmt1 = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt1->bind_param("ii", $amount, $user_id);
        $stmt1->execute();

        // Ghi lịch sử nạp
        $stmt2 = $conn->prepare("INSERT INTO topups (user_id, amount) VALUES (?, ?)");
        $stmt2->bind_param("ii", $user_id, $amount);
        if ($stmt2->execute()) {
            $message = "💰 Nạp $amount vnđ thành công!";
        } else {
            $message = "❌ Lỗi ghi lịch sử: " . $stmt2->error;
        }

        // Cập nhật bảng wallets
        $stmt3 = $conn->prepare("
            INSERT INTO wallets (user_id, balance)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE balance = balance + VALUES(balance)
        ");
        $stmt3->bind_param("ii", $user_id, $amount);
        $stmt3->execute();
    } else {
        $message = "⚠️ Số tiền không hợp lệ!";
    }
}

// Lấy số dư hiện tại
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$balance = $row['balance'];

// Lấy lịch sử nạp tiền (tối đa 5 lần gần nhất)
$history = [];
$stmt = $conn->prepare("SELECT amount, created_at FROM topups WHERE user_id = ? ORDER BY id DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $history[] = $r;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Nạp tiền</title>
    <style>
        body { font-family: Arial; margin: 30px; }
        .box { max-width: 400px; padding: 20px; border: 1px solid #ccc; margin-bottom: 20px; }
        .history { max-width: 400px; padding: 20px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>

<h2>💳 Nạp tiền vào tài khoản</h2>

<div class="box">
    <p>👤 Người dùng: <b><?= htmlspecialchars($_SESSION['username']) ?></b></p>
    <p>💼 Số dư hiện tại: <b><?= number_format($balance) ?> vnđ</b></p>

    <?php if (!empty($message)): ?>
        <p><i><?= $message ?></i></p>
    <?php endif; ?>

    <form method="post">
        <input type="number" name="amount" placeholder="Nhập số tiền muốn nạp" required><br><br>
        <button type="submit">Nạp tiền</button>
    </form>
</div>

<?php if (!empty($history)): ?>
<div class="history">
    <h3>📜 Lịch sử nạp gần nhất</h3>
    <table>
        <tr><th>Số tiền</th><th>Thời gian</th></tr>
        <?php foreach ($history as $h): ?>
        <tr>
            <td><?= number_format($h['amount']) ?> vnđ</td>
            <td><?= $h['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

</body>
</html>
