<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$sql = '';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = webtable_connect();
    $username = $_POST['username'] ?? '';
    $sql = "SELECT id, username, email, password_plain, role, created_at FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Unsafe POST SQL Injection</title><style>body{font-family:monospace;max-width:800px;margin:40px auto;padding:0 20px;}pre{background:#f4f4f4;padding:10px;border:1px solid #ccc;overflow-x:auto;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ccc;padding:8px 12px;text-align:left;}th{background:#eee;}.warn{background:#fff3cd;padding:10px;border:1px solid #ffc107;}</style></head>
<body>
<h1>Unsafe POST SQL Injection</h1>
<div class="warn"><strong>警告：</strong> 此頁面故意使用 SQL 字串串接，存在 SQL Injection 漏洞。僅限本機教學。</div>
<p>使用帳號：<strong>webtable</strong></p>
<form method="post">
<label>Username: <input type="text" name="username" placeholder="e.g. admin' OR '1'='1"></label>
<button type="submit">查詢</button>
</form>
<?php if ($sql): ?>
<h3>執行的 SQL</h3>
<pre><?= htmlspecialchars($sql) ?></pre>
<?php if ($result && $result->num_rows > 0): ?>
<table><thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Password</th><th>Role</th><th>Created</th></tr></thead><tbody>
<?php while ($row = $result->fetch_assoc()): ?>
<tr><td><?= htmlspecialchars((string)$row['id']) ?></td><td><?= htmlspecialchars($row['username']) ?></td><td><?= htmlspecialchars($row['email']) ?></td><td><?= htmlspecialchars($row['password_plain']) ?></td><td><?= htmlspecialchars($row['role']) ?></td><td><?= htmlspecialchars($row['created_at']) ?></td></tr>
<?php endwhile; ?>
</tbody></table>
<?php elseif ($result): ?>
<p>查無資料。</p>
<?php else: ?>
<p>查詢錯誤：<?= htmlspecialchars($conn->error) ?></p>
<?php endif; ?>
<?php endif; ?>
<p><a href="index.php">← 回首頁</a></p>
</body>
</html>
