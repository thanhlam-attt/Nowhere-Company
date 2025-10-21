<?php
session_start();
?>

<style>
nav {
    background-color: #222;
    color: white;
    padding: 10px 20px;
    position: sticky;
    top: 0;
    z-index: 999;
}
nav ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
}
nav ul li {
    margin-right: 20px;
}
nav ul li a {
    color: white;
    text-decoration: none;
    font-weight: bold;
}
nav ul li a:hover {
    text-decoration: underline;
}
.logo {
    font-size: 20px;
    font-weight: bold;
    margin-right: auto;
}
</style>

<nav>
    <ul>
        <li class="logo"><a href="/bug/pages/index.php">🎬 Bug Phim</a></li>
        <li><a href="/bug/pages/index.php">Trang chủ</a></li>
        <li><a href="/bug/pages/forum.php">Gợi ý</a></li>
        <li><a href="/bug/pages/contact.php">Liên hệ</a></li>
        <li>
            <form action="/bug/pages/search.php" method="get" style="display:inline;">
                <input type="text" name="keyword" placeholder="Tìm phim..." required
                    style="padding:5px; border-radius:5px; border:none;">
                <button type="submit" style="padding:5px;">Tìm</button>
            </form>
        </li>

        <?php if (isset($_SESSION['username'])): ?>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <li><a href="/bug/admin/dashboard.php">Quản trị</a></li>
            <?php endif; ?>
            <li><a href="/bug/pages/profile.php?id=<?= $_SESSION['user_id'] ?>">Hồ sơ</a></li>
            <li><a href="/bug/pages/cash.php">Ví: 💰</a></li>
            <li><a href="/bug/main/logout.php">Đăng xuất (<?= $_SESSION['username'] ?>)</a></li>
        <?php else: ?>
            <li><a href="/bug/main/login.php">Đăng nhập</a></li>
            <li><a href="/bug/main/register.php">Đăng ký</a></li>
        <?php endif; ?>
    </ul>
</nav>
