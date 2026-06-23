<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== SQL Injection Lab Setup ===\n\n";

// 1. Connect as root
try {
    $conn = root_connect();
    echo "[OK] connected as root\n";
} catch (Exception $e) {
    die("[FAIL] connected as root: " . $e->getMessage() . "\n");
}

// 2. Create database
$conn->query("CREATE DATABASE IF NOT EXISTS sqli_lab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db('sqli_lab');
echo "[OK] database created or exists\n";

// 3. Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_plain VARCHAR(100) NOT NULL,
    role VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);
echo "[OK] users table created or exists\n";

// 4. Sample data (replace to ensure fresh data)
$conn->query("DELETE FROM users");
$conn->query("ALTER TABLE users AUTO_INCREMENT = 1");
$sample = [
    [1, 'admin', 'admin@sqli-lab.local', 'admin123', 'administrator'],
    [2, 'alice', 'alice@sqli-lab.local', 'alice_pass', 'editor'],
    [3, 'bob', 'bob@sqli-lab.local', 'b0b_P@ss', 'viewer'],
    [4, 'teacher', 'teacher@sqli-lab.local', 'teach_2024', 'instructor'],
    [5, 'student', 'student@sqli-lab.local', 'stud_pass', 'learner'],
];
$stmt = $conn->prepare("INSERT INTO users (id, username, email, password_plain, role) VALUES (?, ?, ?, ?, ?)");
foreach ($sample as $row) {
    $stmt->bind_param('issss', $row[0], $row[1], $row[2], $row[3], $row[4]);
    $stmt->execute();
}
$stmt->close();
echo "[OK] sample data inserted or refreshed\n";

// 5. Create MySQL users
$users = [
    ['sp_owner', 'sp_owner_pass'],
    ['webtable', 'webtable_pass'],
    ['websp', 'websp_pass'],
];
foreach ($users as $u) {
    $conn->query("DROP USER IF EXISTS '{$u[0]}'@'%'");
    $conn->query("CREATE USER '{$u[0]}'@'%' IDENTIFIED BY '{$u[1]}'");
    echo "[OK] {$u[0]} created or exists\n";
}

// 6. Create stored procedures
$procedures = <<<'HEREDOC_SQL'
USE sqli_lab;

DELIMITER //

DROP PROCEDURE IF EXISTS sp_get_user_by_id//
CREATE PROCEDURE sp_get_user_by_id(IN p_id INT)
    SQL SECURITY DEFINER
    DEFINER = 'sp_owner'@'%'
BEGIN
    SELECT id, username, email, password_plain, role, created_at
    FROM users
    WHERE id = p_id;
END//

DROP PROCEDURE IF EXISTS sp_search_users//
CREATE PROCEDURE sp_search_users(IN p_keyword VARCHAR(100))
    SQL SECURITY DEFINER
    DEFINER = 'sp_owner'@'%'
BEGIN
    SELECT id, username, email, password_plain, role, created_at
    FROM users
    WHERE username LIKE CONCAT('%', p_keyword, '%')
       OR email LIKE CONCAT('%', p_keyword, '%');
END//

DROP PROCEDURE IF EXISTS sp_create_user//
CREATE PROCEDURE sp_create_user(
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_password_plain VARCHAR(100),
    IN p_role VARCHAR(30)
)
    SQL SECURITY DEFINER
    DEFINER = 'sp_owner'@'%'
    MODIFIES SQL DATA
BEGIN
    INSERT INTO users (username, email, password_plain, role)
    VALUES (p_username, p_email, p_password_plain, p_role);
    SELECT LAST_INSERT_ID() AS new_id;
END//

DROP PROCEDURE IF EXISTS sp_search_users_unsafe//
CREATE PROCEDURE sp_search_users_unsafe(IN p_keyword VARCHAR(100))
    SQL SECURITY DEFINER
    DEFINER = 'sp_owner'@'%'
BEGIN
    SET @sql = CONCAT(
        'SELECT id, username, email, password_plain, role, created_at FROM users WHERE username LIKE ''%',
        p_keyword,
        '%'' OR email LIKE ''%',
        p_keyword,
        '%'''
    );
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END//

DELIMITER ;
HEREDOC_SQL;

if ($conn->multi_query($procedures)) {
    while ($conn->next_result()) {;}
    echo "[OK] procedures created\n";
} else {
    echo "[FAIL] procedures: " . $conn->error . "\n";
}

// 7. Grants
$grants = [
    "GRANT SELECT, INSERT, UPDATE, DELETE ON sqli_lab.users TO 'sp_owner'@'%'",
    "GRANT CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON sqli_lab.* TO 'sp_owner'@'%'",
    "GRANT SELECT, INSERT, UPDATE, DELETE ON sqli_lab.users TO 'webtable'@'%'",
    "GRANT EXECUTE ON PROCEDURE sqli_lab.sp_get_user_by_id TO 'websp'@'%'",
    "GRANT EXECUTE ON PROCEDURE sqli_lab.sp_search_users TO 'websp'@'%'",
    "GRANT EXECUTE ON PROCEDURE sqli_lab.sp_create_user TO 'websp'@'%'",
    "GRANT EXECUTE ON PROCEDURE sqli_lab.sp_search_users_unsafe TO 'websp'@'%'",
];
foreach ($grants as $g) {
    $conn->query($g);
}
$conn->query("FLUSH PRIVILEGES");
echo "[OK] grants applied\n";

// 8. Permission check
$conn->close();

echo "\n=== Permission Check ===\n\n";

// Test websp
$websp = new mysqli(
    $env['DB_HOST'],
    $env['WEBSP_USER'],
    $env['WEBSP_PASSWORD'],
    $env['MYSQL_DATABASE']
);

$tests_websp = [
    'SELECT users' => "SELECT COUNT(*) FROM users",
    'INSERT users' => "INSERT INTO users (username,email,password_plain,role) VALUES ('t','t@t.com','t','t')",
    'UPDATE users' => "UPDATE users SET username='x' WHERE id=999",
    'DELETE users' => "DELETE FROM users WHERE id=999",
];
foreach ($tests_websp as $label => $sql) {
    $r = $websp->query($sql);
    if ($r === false && $websp->errno === 1142) {
        echo "[OK] websp {$label} → FAIL (expected)\n";
    } elseif ($r === false) {
        echo "[OK] websp {$label} → FAIL ({$websp->error})\n";
    } else {
        echo "[WARN] websp {$label} → OK (unexpected access!)\n";
    }
}

$tests_sp = [
    'sp_get_user_by_id' => "CALL sp_get_user_by_id(1)",
    'sp_search_users' => "CALL sp_search_users('a')",
    'sp_create_user' => "CALL sp_create_user('test','t@t.com','t','t')",
    'sp_search_users_unsafe' => "CALL sp_search_users_unsafe('a')",
];
foreach ($tests_sp as $label => $sql) {
    if ($websp->multi_query($sql)) {
        while ($websp->next_result()) {;}
        echo "[OK] websp CALL {$label} → OK\n";
    } else {
        echo "[FAIL] websp CALL {$label} → FAIL ({$websp->error})\n";
    }
}
$websp->close();

// Test webtable
$webtable = new mysqli(
    $env['DB_HOST'],
    $env['WEBTABLE_USER'],
    $env['WEBTABLE_PASSWORD'],
    $env['MYSQL_DATABASE']
);

$r = $webtable->query("SELECT COUNT(*) AS cnt FROM users");
echo ($r !== false) ? "[OK] webtable SELECT users → OK\n" : "[FAIL] webtable SELECT users → FAIL\n";

$tests_webtable_sp = [
    'sp_get_user_by_id' => "CALL sp_get_user_by_id(1)",
    'sp_search_users' => "CALL sp_search_users('a')",
    'sp_create_user' => "CALL sp_create_user('t','t@t.com','t','t')",
    'sp_search_users_unsafe' => "CALL sp_search_users_unsafe('a')",
];
foreach ($tests_webtable_sp as $label => $sql) {
    if ($webtable->multi_query($sql)) {
        while ($webtable->next_result()) {;}
        echo "[WARN] webtable CALL {$label} → OK (unexpected access!)\n";
    } else {
        echo "[OK] webtable CALL {$label} → FAIL (expected)\n";
    }
}
$webtable->close();

echo "\n=== Setup Complete ===\n";
