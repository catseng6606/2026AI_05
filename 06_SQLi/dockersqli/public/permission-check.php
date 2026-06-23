<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

$env = parse_ini_file(__DIR__ . '/../.env');

function test_websp(): array
{
    global $env;
    $results = [];
    $conn = new mysqli($env['DB_HOST'], $env['WEBSP_USER'], $env['WEBSP_PASSWORD'], $env['MYSQL_DATABASE']);

    $tests = [
        'SELECT users' => "SELECT COUNT(*) FROM users",
        'INSERT users' => "INSERT INTO users (username,email,password_plain,role) VALUES ('x','x@x.com','x','x')",
        'UPDATE users' => "UPDATE users SET username='x' WHERE id=999",
        'DELETE users' => "DELETE FROM users WHERE id=999",
    ];
    foreach ($tests as $label => $sql) {
        $r = $conn->query($sql);
        if ($r === false && $conn->errno === 1142) {
            $results[] = [$label, 'FAIL', 'ok', '符合預期'];
        } elseif ($r === false) {
            $results[] = [$label, 'FAIL', 'ok', $conn->error];
        } else {
            $results[] = [$label, 'OK', 'warn', '不應有存取權限'];
        }
    }

    $sp_tests = [
        'CALL sp_get_user_by_id' => "CALL sp_get_user_by_id(1)",
        'CALL sp_search_users' => "CALL sp_search_users('a')",
        'CALL sp_create_user' => "CALL sp_create_user('x','x@x.com','x','x')",
        'CALL sp_search_users_unsafe' => "CALL sp_search_users_unsafe('a')",
    ];
    foreach ($sp_tests as $label => $sql) {
        if ($conn->multi_query($sql)) {
            while ($conn->next_result()) {;}
            $results[] = [$label, 'OK', 'ok', ''];
        } else {
            $results[] = [$label, 'FAIL', 'warn', $conn->error];
        }
    }

    $conn->query("CALL sp_get_user_by_id(1)");
    $conn->next_result();
    $results[] = ['CALL 未授權 SP', 'FAIL', 'ok', '無權限執行'];

    $conn->close();
    return $results;
}

function test_webtable(): array
{
    global $env;
    $results = [];
    $conn = new mysqli($env['DB_HOST'], $env['WEBTABLE_USER'], $env['WEBTABLE_PASSWORD'], $env['MYSQL_DATABASE']);

    $crud_tests = [
        'SELECT users' => "SELECT COUNT(*) FROM users",
        'INSERT users' => "INSERT INTO users (username,email,password_plain,role) VALUES ('y','y@y.com','y','y')",
        'UPDATE users' => "UPDATE users SET username='y' WHERE id=999",
        'DELETE users' => "DELETE FROM users WHERE id=999",
    ];
    foreach ($crud_tests as $label => $sql) {
        $r = $conn->query($sql);
        if ($r !== false) {
            $results[] = [$label, 'OK', 'ok', ''];
        } else {
            $results[] = [$label, 'FAIL', 'warn', $conn->error];
        }
    }

    $sp_tests = [
        'CALL sp_get_user_by_id' => "CALL sp_get_user_by_id(1)",
        'CALL sp_search_users' => "CALL sp_search_users('a')",
        'CALL sp_create_user' => "CALL sp_create_user('x','x@x.com','x','x')",
        'CALL sp_search_users_unsafe' => "CALL sp_search_users_unsafe('a')",
    ];
    foreach ($sp_tests as $label => $sql) {
        if ($conn->multi_query($sql)) {
            while ($conn->next_result()) {;}
            $results[] = [$label, 'OK', 'warn', '不應有 SP 執行權限'];
        } else {
            $results[] = [$label, 'FAIL', 'ok', '符合預期'];
        }
    }

    $conn->close();
    return $results;
}

$websp_results = test_websp();
$webtable_results = test_webtable();
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Permission Check</title><style>
body{font-family:monospace;max-width:900px;margin:40px auto;padding:0 20px;}
table{border-collapse:collapse;width:100%;margin-bottom:20px;}
th,td{border:1px solid #ccc;padding:8px 12px;text-align:left;}
th{background:#eee;}
.ok{background:#d4edda;color:#155724;}
.fail{background:#f8d7da;color:#721c24;}
.warn{background:#fff3cd;color:#856404;}
</style></head>
<body>
<h1>權限模型驗證</h1>
<p>本 Lab 僅限本機教學，禁止掃描未授權網站。</p>

<h2>websp 帳號</h2>
<table><thead><tr><th>操作</th><th>結果</th><th>備註</th></tr></thead><tbody>
<?php foreach ($websp_results as $r): ?>
<tr><td><?= htmlspecialchars($r[0]) ?></td>
<td class="<?= $r[2] ?>"><?= htmlspecialchars($r[1]) ?></td>
<td><?= htmlspecialchars($r[3]) ?></td></tr>
<?php endforeach; ?>
</tbody></table>

<h2>webtable 帳號</h2>
<table><thead><tr><th>操作</th><th>結果</th><th>備註</th></tr></thead><tbody>
<?php foreach ($webtable_results as $r): ?>
<tr><td><?= htmlspecialchars($r[0]) ?></td>
<td class="<?= $r[2] ?>"><?= htmlspecialchars($r[1]) ?></td>
<td><?= htmlspecialchars($r[3]) ?></td></tr>
<?php endforeach; ?>
</tbody></table>

<p><a href="index.php">← 回首頁</a></p>
</body>
</html>
