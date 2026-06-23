<?php
require __DIR__ . '/config.php';
page_header('POST Unsafe SQL Injection');

$username = $_POST['username'] ?? 'alice';
$sql = "SELECT id, username, email, password_plain, role, created_at FROM users WHERE username = '{$username}'";
?>
<p>DB user: <code>webtable</code></p>
<form method="post">
  <label>username <input name="username" value="<?= h($username) ?>"></label>
  <button type="submit">Search</button>
</form>
<p>Try value: <code>' OR '1'='1</code></p>
<pre><?= h($sql) ?></pre>
<?php
try {
    render_rows(webtable_pdo()->query($sql)->fetchAll());
} catch (Throwable $e) {
    echo '<p class="fail">' . h($e->getMessage()) . '</p>';
}
page_footer();
