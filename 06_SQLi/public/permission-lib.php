<?php
function check_query(PDO $pdo, string $sql): bool
{
    $started = false;
    try {
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
            $started = true;
        }
        $pdo->exec($sql);
        if ($started) {
            $pdo->rollBack();
        }
        return true;
    } catch (Throwable) {
        if ($started && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}

function permission_results(): array
{
    $websp = websp_pdo();
    $webtable = webtable_pdo();
    return [
        'websp' => [
            'SELECT users' => check_query($websp, 'SELECT * FROM users LIMIT 1'),
            'INSERT users' => check_query($websp, "INSERT INTO users(username,email,password_plain,role) VALUES('x','x@example.test','x','x')"),
            'UPDATE users' => check_query($websp, "UPDATE users SET role='x' WHERE id=-1"),
            'DELETE users' => check_query($websp, 'DELETE FROM users WHERE id=-1'),
            'CALL sp_get_user_by_id' => check_query($websp, 'CALL sp_get_user_by_id(1)'),
            'CALL sp_search_users' => check_query($websp, "CALL sp_search_users('a')"),
            'CALL sp_create_user' => check_query($websp, "CALL sp_create_user('temp','temp@example.test','temp','temp')"),
            'CALL sp_search_users_unsafe' => check_query($websp, "CALL sp_search_users_unsafe('a')"),
            'CALL missing SP' => check_query($websp, 'CALL sp_missing()'),
        ],
        'webtable' => [
            'SELECT users' => check_query($webtable, 'SELECT * FROM users LIMIT 1'),
            'INSERT users' => check_query($webtable, "INSERT INTO users(username,email,password_plain,role) VALUES('table_temp','t@example.test','t','t')"),
            'UPDATE users' => check_query($webtable, "UPDATE users SET role='member' WHERE id=-1"),
            'DELETE users' => check_query($webtable, 'DELETE FROM users WHERE id=-1'),
            'CALL sp_get_user_by_id' => check_query($webtable, 'CALL sp_get_user_by_id(1)'),
            'CALL sp_search_users' => check_query($webtable, "CALL sp_search_users('a')"),
            'CALL sp_create_user' => check_query($webtable, "CALL sp_create_user('x','x@example.test','x','x')"),
            'CALL sp_search_users_unsafe' => check_query($webtable, "CALL sp_search_users_unsafe('a')"),
        ],
    ];
}
