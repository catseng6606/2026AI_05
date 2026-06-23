<?php
require __DIR__ . '/config.php';
page_header('GET Unsafe SQL Injection');

$id = $_GET['id'] ?? '1';
$sql = "SELECT id, username, email, password_plain, role, created_at FROM users WHERE id = {$id}";
echo '<p>DB user: <code>webtable</code></p>';
echo '<p>Try: <a href="/unsafe.php?id=1">?id=1</a> or <a href="/unsafe.php?id=1 OR 1=1">?id=1 OR 1=1</a></p>';
echo '<pre>' . h($sql) . '</pre>';

try {
    $rows = webtable_pdo()->query($sql)->fetchAll();
    render_rows($rows);
} catch (Throwable $e) {
    echo '<p class="fail">' . h($e->getMessage()) . '</p>';
}

page_footer();
