<?php
require __DIR__ . '/config.php';
page_header('Safe PDO Prepared Statement');

$id = $_GET['id'] ?? '1';
$sql = 'SELECT id, username, email, role, created_at FROM users WHERE id = ?';
echo '<p>DB user: <code>webtable</code></p>';
echo '<p>Prepared Statement 會把輸入當資料，不會拼進 SQL 語法。</p>';
echo '<pre>' . h($sql) . "\nparam: " . h($id) . '</pre>';

try {
    $stmt = webtable_pdo()->prepare($sql);
    $stmt->execute([$id]);
    render_rows($stmt->fetchAll());
} catch (Throwable $e) {
    echo '<p class="fail">' . h($e->getMessage()) . '</p>';
}

page_footer();
