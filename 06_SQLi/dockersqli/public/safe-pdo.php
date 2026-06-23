<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$env = parse_ini_file(__DIR__ . '/../.env');
$rows = [];
$error = '';
$id = $_GET['id'] ?? '1';

try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};dbname={$env['MYSQL_DATABASE']};charset=utf8mb4",
        $env['WEBTABLE_USER'],
        $env['WEBTABLE_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $stmt = $pdo->prepare("SELECT id, username, email, password_plain, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Safe PDO Prepared Statement</title><style>body{font-family:monospace;max-width:800px;margin:40px auto;padding:0 20px;}pre{background:#f4f4f4;padding:10px;border:1px solid #ccc;overflow-x:auto;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ccc;padding:8px 12px;text-align:left;}th{background:#eee;}.info{background:#d1ecf1;padding:10px;border:1px solid #17a2b8;}</style></head>
<body>
<h1>Safe PDO Prepared Statement</h1>
<div class="info"><strong>說明：</strong> 此頁面使用 PDO Prepared Statement，防止 SQL Injection。但仍直接存取 <code>users</code> table（使用 webtable 帳號）。這是語法層防護，不是權限層防護。</div>
<p>使用帳號：<strong>webtable</strong>（直連 table）</p>
<p>範例：<a href="?id=1">?id=1</a> | <a href="?id=2">?id=2</a> | <a href="?id=1 OR 1=1">?id=1 OR 1=1（應無效）</a></p>
<h3>使用的 Prepared Statement</h3>
<pre>SELECT id, username, email, password_plain, role, created_at FROM users WHERE id = ?</pre>
<h3>參數值</h3>
<pre>id = <?= htmlspecialchars($id) ?></pre>
<?php if ($error): ?>
<p>錯誤：<?= htmlspecialchars($error) ?></p>
<?php elseif (count($rows) > 0): ?>
<table><thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Password</th><th>Role</th><th>Created</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?>
<tr><td><?= htmlspecialchars((string)$row['id']) ?></td><td><?= htmlspecialchars($row['username']) ?></td><td><?= htmlspecialchars($row['email']) ?></td><td><?= htmlspecialchars($row['password_plain']) ?></td><td><?= htmlspecialchars($row['role']) ?></td><td><?= htmlspecialchars($row['created_at']) ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?php else: ?>
<p>查無資料。</p>
<?php endif; ?>
<p><a href="index.php">← 回首頁</a></p>
</body>
</html>
