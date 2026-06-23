<?php
require __DIR__ . '/config.php';
page_header('Unsafe Dynamic SQL in Stored Procedure');

$keyword = $_GET['keyword'] ?? 'a';
echo '<p>DB user: <code>websp</code>。這頁只能 CALL SP，但 SP 內部用 CONCAT + PREPARE 組 SQL，所以仍可被注入。</p>';
echo '<p>Try: <a href="/sp-unsafe.php?keyword=a">keyword=a</a> or <a href="/sp-unsafe.php?keyword=%27 OR 1=1 -- ">keyword=\' OR 1=1 -- </a></p>';
echo '<pre>CALL sp_search_users_unsafe(' . h($keyword) . ')</pre>';

try {
    $stmt = websp_pdo()->prepare('CALL sp_search_users_unsafe(?)');
    $stmt->execute([$keyword]);
    render_rows($stmt->fetchAll());
} catch (Throwable $e) {
    echo '<p class="fail">' . h($e->getMessage()) . '</p>';
}

page_footer();
