<?php
require_once __DIR__ . '/db.php';
$keyword = $_POST['keyword'] ?? '';
$rows = [];
$sql = '';
if ($keyword !== '') {
    $sql = "SELECT id, username, email, role FROM users WHERE username LIKE '%$keyword%'";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>Unsafe SQL Injection (POST)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Unsafe SQL Injection (POST)</h1>
        <div class="danger">危險：此頁面直接串接 SQL，容易遭受 SQL Injection 攻擊</div>
        <form method="POST">
            <label>Keyword: <input type="text" name="keyword" value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"></label>
            <button type="submit">Search</button>
        </form>
        <?php if ($keyword !== ''): ?>
        <h2>執行的 SQL</h2>
        <pre><?= htmlspecialchars($sql, ENT_QUOTES, 'UTF-8') ?></pre>
        <h2>查詢結果</h2>
        <table>
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
        <div class="footer">SQL Injection Teaching Lab - 僅限本機 Docker Lab，禁止未授權測試</div>
    </div>
</body>
</html>
