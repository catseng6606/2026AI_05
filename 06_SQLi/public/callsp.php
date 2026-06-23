<?php
require __DIR__ . '/config.php';
page_header('Stored Procedure + Least Privilege');

$id = $_GET['id'] ?? '1';
$keyword = $_GET['keyword'] ?? 'a';
$pdo = websp_pdo();
echo '<p>DB user: <code>websp</code>，不可直接查 table，只能 CALL 指定 SP。</p>';

try {
    echo '<h2>Direct table access</h2>';
    $pdo->query('SELECT * FROM users LIMIT 1');
    echo '<p class="fail">Unexpected: direct SELECT succeeded.</p>';
} catch (Throwable $e) {
    echo '<p class="ok">Expected FAIL: ' . h($e->getMessage()) . '</p>';
}

try {
    echo '<h2>CALL sp_get_user_by_id</h2>';
    $stmt = $pdo->prepare('CALL sp_get_user_by_id(?)');
    $stmt->execute([$id]);
    render_rows($stmt->fetchAll());
    $stmt->closeCursor();

    echo '<h2>CALL sp_search_users</h2>';
    $stmt = $pdo->prepare('CALL sp_search_users(?)');
    $stmt->execute([$keyword]);
    render_rows($stmt->fetchAll());
} catch (Throwable $e) {
    echo '<p class="fail">' . h($e->getMessage()) . '</p>';
}

page_footer();
