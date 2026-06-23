<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$conn = websp_connect();
$mode = $_GET['mode'] ?? 'by_id';
$rows = [];
$error = '';

if ($mode === 'by_id') {
    $id = (int)($_GET['id'] ?? 1);
    $stmt = $conn->prepare("CALL sp_get_user_by_id(?)");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        $stmt->close();
        while ($conn->next_result()) {;}
    } else {
        $error = $conn->error;
    }
} elseif ($mode === 'search') {
    $keyword = $_GET['keyword'] ?? '';
    $stmt = $conn->prepare("CALL sp_search_users(?)");
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
<head><meta charset="UTF-8"><title>Stored Procedure Demo</title><style>body{font-family:monospace;max-width:800px;margin:40px auto;padding:0 20px;}pre{background:#f4f4f4;padding:10px;border:1px solid #ccc;overflow-x:auto;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ccc;padding:8px 12px;text-align:left;}th{background:#eee;}.info{background:#d1ecf1;padding:10px;border:1px solid #17a2b8;}</style></head>
<body>
<h1>Stored Procedure + 最小權限</h1>
<div class="info">
<strong>重點：</strong>
<ul>
<li>使用帳號 <strong>websp</strong>，無 table CRUD 權限。</li>
<li>websp 只能 CALL 已授權的 SP。</li>
<li>SP 使用 <code>SQL SECURITY DEFINER</code>，以 <strong>sp_owner</strong> 權限執行。</li>
</ul>
</div>
<p>使用帳號：<strong>websp</strong>（僅能 CALL SP）</p>
<p>
模式：
<a href="?mode=by_id&id=1">sp_get_user_by_id</a> |
<a href="?mode=search&keyword=a">sp_search_users</a>
</p>
<?php if ($mode === 'by_id'): ?>
<h3>CALL sp_get_user_by_id(<?= (int)($id ?? 1) ?>)</h3>
<?php elseif ($mode === 'search'): ?>
<h3>CALL sp_search_users('<?= htmlspecialchars($keyword ?? '') ?>')</h3>
<?php endif; ?>
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
