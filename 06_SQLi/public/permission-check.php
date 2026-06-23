<?php
require __DIR__ . '/config.php';
require __DIR__ . '/permission-lib.php';
page_header('Permission Check');

$expected = [
    'websp' => [
        'SELECT users' => false, 'INSERT users' => false, 'UPDATE users' => false, 'DELETE users' => false,
        'CALL sp_get_user_by_id' => true, 'CALL sp_search_users' => true, 'CALL sp_create_user' => true,
        'CALL sp_search_users_unsafe' => true, 'CALL missing SP' => false,
    ],
    'webtable' => [
        'SELECT users' => true, 'INSERT users' => true, 'UPDATE users' => true, 'DELETE users' => true,
        'CALL sp_get_user_by_id' => false, 'CALL sp_search_users' => false, 'CALL sp_create_user' => false,
        'CALL sp_search_users_unsafe' => false,
    ],
];

foreach (permission_results() as $user => $items) {
    echo '<h2>' . h($user) . '</h2><table><tr><th>Action</th><th>Actual</th><th>Expected</th></tr>';
    foreach ($items as $name => $actual) {
        $want = $expected[$user][$name];
        $class = $actual === $want ? 'ok' : 'fail';
        echo '<tr><td>' . h($name) . '</td><td class="' . $class . '">' . ($actual ? 'OK' : 'FAIL') . '</td><td>' . ($want ? 'OK' : 'FAIL') . '</td></tr>';
    }
    echo '</table>';
}

page_footer();
