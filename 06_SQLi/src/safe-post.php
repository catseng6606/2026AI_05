<?php
require_once __DIR__ . '/db.php';
$keyword = $_POST['keyword'] ?? '';
$rows = [];
if ($keyword !== '') {
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE username LIKE :keyword");
    $stmt->execute([':keyword' => '%' . $keyword . '%']);
    $rows = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>Safe PDO Prepared Statement (POST)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Safe PDO Prepared Statement (POST)</h1>
        <div class="safe">安全：使用 PDO Prepared Statement，防止 SQL Injection</div>
        <form method="POST">
            <label>Keyword: <input type="text" name="keyword" value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"></label>
            <button type="submit">Search</button>
        </form>
        <?php if ($keyword !== ''): ?>
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
