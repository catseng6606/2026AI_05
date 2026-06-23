<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$conn = websp_connect();
$keyword = $_GET['keyword'] ?? '';
$rows = [];
$error = '';

if ($keyword !== '') {
    $stmt = $conn->prepare("CALL sp_search_users_unsafe(?)");
    if ($stmt) {
        $stmt->bind_param('s', $keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        $stmt->close();
        while ($conn->next_result()) {;}
    } else {
        $error = $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>SP Unsafe Dynamic SQL</title><style>body{font-family:monospace;max-width:800px;margin:40px auto;padding:0 20px;}pre{background:#f4f4f4;padding:10px;border:1px solid #ccc;overflow-x:auto;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ccc;padding:8px 12px;text-align:left;}th{background:#eee;}.warn{background:#f8d7da;padding:10px;border:1px solid #f5c6cb;}</style></head>
<body>
<h1>Stored Procedure 內動態 SQL Injection</h1>
<div class="warn">
<strong>警告：</strong> 此頁面使用 <code>sp_search_users_unsafe</code>，該 SP 內部使用 <code>CONCAT</code> + <code>PREPARE</code> + <code>EXECUTE</code> 串接輸入參數，存在 SQL Injection 漏洞。
<br><strong>Stored Procedure 不是自動安全！</strong> 內部若使用動態 SQL 串接輸入，仍可能 SQL Injection。
</div>
<p>使用帳號：<strong>websp</strong>（僅能 CALL SP）</p>
<form method="get">
<label>Keyword: <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>"></label>
<button type="submit">搜尋</button>
</form>
<p>嘗試：<code>?keyword=' OR 1=1 -- </code></p>
<?php if ($keyword !== ''): ?>
<h3>CALL sp_search_users_unsafe('<?= htmlspecialchars($keyword) ?>')</h3>
<p>SP 內部執行動態 SQL：</p>
<pre>CONCAT('SELECT ... WHERE username LIKE ''%', p_keyword, '%'' OR email LIKE ''%', p_keyword, '%''')</pre>
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
<?php endif; ?>
<p><a href="index.php">← 回首頁</a></p>
</body>
</html>
