<?php
require __DIR__ . '/config.php';
page_header('Setup');

$db = envv('DB_NAME', 'sqli_lab');
$spOwner = envv('SP_OWNER_USER', 'sp_owner');
$spOwnerPass = envv('SP_OWNER_PASSWORD', 'sp_owner_pass');
$webtable = envv('WEBTABLE_USER', 'webtable');
$webtablePass = envv('WEBTABLE_PASSWORD', 'webtable_pass');
$websp = envv('WEBSP_USER', 'websp');
$webspPass = envv('WEBSP_PASSWORD', 'websp_pass');

function step(string $label, callable $fn): void
{
    try {
        $fn();
        echo '<div class="ok">[OK] ' . h($label) . '</div>';
    } catch (Throwable $e) {
        echo '<div class="fail">[FAIL] ' . h($label) . ': ' . h($e->getMessage()) . '</div>';
    }
}

$root = root_pdo(null);
step('connected as root', fn() => $root->query('SELECT 1'));

step('database created or exists', function () use ($root, $db) {
    $root->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $root->exec("USE `{$db}`");
});

step('users table created or exists', function () use ($root, $db) {
    $root->exec("USE `{$db}`");
    $root->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            password_plain VARCHAR(100) NOT NULL,
            role VARCHAR(30) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
});

step('sample data inserted or refreshed', function () use ($root, $db) {
    $root->exec("USE `{$db}`");
    $root->exec('TRUNCATE TABLE users');
    $stmt = $root->prepare('INSERT INTO users (username,email,password_plain,role) VALUES (?,?,?,?)');
    foreach ([
        ['admin', 'admin@example.test', 'admin123', 'admin'],
        ['alice', 'alice@example.test', 'alice123', 'member'],
        ['bob', 'bob@example.test', 'bob123', 'member'],
        ['teacher', 'teacher@example.test', 'teach123', 'teacher'],
        ['student', 'student@example.test', 'student123', 'student'],
    ] as $row) {
        $stmt->execute($row);
    }
});

foreach ([[$spOwner, $spOwnerPass], [$webtable, $webtablePass], [$websp, $webspPass]] as [$user, $pass]) {
    step("{$user} created or exists", function () use ($root, $user, $pass) {
        $root->exec("CREATE USER IF NOT EXISTS '{$user}'@'%' IDENTIFIED BY '{$pass}'");
        $root->exec("ALTER USER '{$user}'@'%' IDENTIFIED BY '{$pass}'");
    });
}

step('procedures created', function () use ($root, $db, $spOwner) {
    $root->exec("USE `{$db}`");
    foreach (['sp_get_user_by_id', 'sp_search_users', 'sp_create_user', 'sp_search_users_unsafe'] as $proc) {
        $root->exec("DROP PROCEDURE IF EXISTS {$proc}");
    }
    $root->exec("
        CREATE DEFINER='{$spOwner}'@'%' PROCEDURE sp_get_user_by_id(IN p_id INT)
        SQL SECURITY DEFINER
        SELECT id, username, email, role, created_at FROM users WHERE id = p_id
    ");
    $root->exec("
        CREATE DEFINER='{$spOwner}'@'%' PROCEDURE sp_search_users(IN p_keyword VARCHAR(100))
        SQL SECURITY DEFINER
        SELECT id, username, email, role, created_at
        FROM users
        WHERE username LIKE CONCAT('%', p_keyword, '%')
           OR email LIKE CONCAT('%', p_keyword, '%')
    ");
    $root->exec("
        CREATE DEFINER='{$spOwner}'@'%' PROCEDURE sp_create_user(
            IN p_username VARCHAR(50),
            IN p_email VARCHAR(100),
            IN p_password_plain VARCHAR(100),
            IN p_role VARCHAR(30)
        )
        SQL SECURITY DEFINER
        INSERT INTO users (username, email, password_plain, role)
        VALUES (p_username, p_email, p_password_plain, p_role)
    ");
    $root->exec("
        CREATE DEFINER='{$spOwner}'@'%' PROCEDURE sp_search_users_unsafe(IN p_keyword VARCHAR(100))
        SQL SECURITY DEFINER
        BEGIN
            SET @sql = CONCAT(
                'SELECT id, username, email, role, created_at FROM users ',
                'WHERE username LIKE ''%', p_keyword, '%'' OR email LIKE ''%', p_keyword, '%'''
            );
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        END
    ");
});

step('grants applied', function () use ($root, $db, $spOwner, $webtable, $websp) {
    $root->exec("REVOKE ALL PRIVILEGES, GRANT OPTION FROM '{$spOwner}'@'%'");
    $root->exec("REVOKE ALL PRIVILEGES, GRANT OPTION FROM '{$webtable}'@'%'");
    $root->exec("REVOKE ALL PRIVILEGES, GRANT OPTION FROM '{$websp}'@'%'");
    $root->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON `{$db}`.users TO '{$spOwner}'@'%'");
    $root->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON `{$db}`.users TO '{$webtable}'@'%'");
    foreach (['sp_get_user_by_id', 'sp_search_users', 'sp_create_user', 'sp_search_users_unsafe'] as $proc) {
        $root->exec("GRANT EXECUTE ON PROCEDURE `{$db}`.{$proc} TO '{$websp}'@'%'");
    }
    $root->exec('FLUSH PRIVILEGES');
});

step('permission check completed', fn() => require __DIR__ . '/permission-lib.php');

echo '<p><a href="/permission-check.php">Open detailed permission check</a></p>';
page_footer();
