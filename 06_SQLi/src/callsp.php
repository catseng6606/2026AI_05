<?php
require_once __DIR__ . '/db.php';
$action = $_GET['action'] ?? 'list';
$rows = [];
$message = '';

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->prepare("CALL sp_user_list()");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            break;
        case 'get':
            $stmt = $pdo->prepare("CALL sp_user_get(:id)");
            $stmt->execute([':id' => (int)($_GET['id'] ?? 1)]);
            $rows = $stmt->fetchAll();
            break;
        case 'create':
            $stmt = $pdo->prepare("CALL sp_user_create(:username, :email, :role)");
            $stmt->execute([
                ':username' => $_GET['username'] ?? '',
                ':email' => $_GET['email'] ?? '',
                ':role' => $_GET['role'] ?? 'student',
            ]);
            $message = 'User created';
            break;
        case 'update':
            $stmt = $pdo->prepare("CALL sp_user_update(:id, :username, :email, :role)");
            $stmt->execute([
                ':id' => (int)($_GET['id'] ?? 1),
                ':username' => $_GET['username'] ?? '',
                ':email' => $_GET['email'] ?? '',
                ':role' => $_GET['role'] ?? 'student',
            ]);
            $message = 'User updated';
            break;
        case 'delete':
            $stmt = $pdo->prepare("CALL sp_user_delete(:id)");
            $stmt->execute([':id' => (int)($_GET['id'] ?? 1)]);
            $message = 'User deleted';
            break;
        default:
            $message = 'Unknown action';
    }
} catch (Exception $e) {
    $message = 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>Stored Procedure Demo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Stored Procedure Demo</h1>
        <div class="safe">安全：使用 PDO Prepared Statement 呼叫 SP，無字串串接</div>
        <h2>操作</h2>
        <ul>
            <li><a href="?action=list">List Users</a></li>
            <li><a href="?action=get&id=1">Get User ID=1</a></li>
            <li><a href="?action=create&username=test&email=test@example.test&role=student">Create User</a></li>
            <li><a href="?action=update&id=1&username=updated&email=updated@example.test&role=teacher">Update User ID=1</a></li>
            <li><a href="?action=delete&id=6">Delete User ID=6</a></li>
        </ul>
        <?php if ($message): ?>
        <p><strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong></p>
        <?php endif; ?>
        <?php if ($rows): ?>
        <table>
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created At</th></tr>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
        <div class="footer">SQL Injection Teaching Lab - 僅限本機 Docker Lab，禁止未授權測試</div>
    </div>
</body>
</html>
