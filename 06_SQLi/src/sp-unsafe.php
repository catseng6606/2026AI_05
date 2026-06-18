<?php
require_once __DIR__ . '/db.php';
$keyword = $_GET['keyword'] ?? 'a';
$rows = [];
$error = '';
try {
    $stmt = $pdo->prepare("CALL sp_user_search_unsafe(:keyword)");
    $stmt->execute([':keyword' => $keyword]);
    $rows = $stmt->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>SP Unsafe Demo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Stored Procedure Unsafe Demo</h1>
        <div class="danger">危險：PHP 使用 Prepared Statement，但 SP 內部使用 CONCAT 動態 SQL，仍可能 SQL Injection</div>
        <h2>說明</h2>
        <p>此頁面使用 Prepared Statement 呼叫 SP：</p>
        <pre>CALL sp_user_search_unsafe(:keyword)</pre>
        <p>但 SP 內部使用 CONCAT 組動態 SQL：</p>
        <pre>SET @sql = CONCAT("SELECT ... WHERE username LIKE '%", p_keyword, "%'");</pre>
        <p>因此即使 PHP 端安全，SP 內部仍存在 SQL Injection 風險。</p>
        <h2>查詢結果</h2>
        <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
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
        <div class="footer">SQL Injection Teaching Lab - 僅限本機 Docker Lab，禁止未授權測試</div>
    </div>
</body>
</html>
