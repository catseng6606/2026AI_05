<?php
declare(strict_types=1);

function envv(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function pdo_connect(string $user, string $password, ?string $db = null): PDO
{
    $host = envv('DB_HOST', 'mysql');
    $name = $db ?? envv('DB_NAME', 'sqli_lab');
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function root_pdo(?string $db = null): PDO
{
    return pdo_connect('root', envv('MYSQL_ROOT_PASSWORD', 'rootpass'), $db);
}

function webtable_pdo(): PDO
{
    return pdo_connect(envv('WEBTABLE_USER', 'webtable'), envv('WEBTABLE_PASSWORD', 'webtable_pass'));
}

function websp_pdo(): PDO
{
    return pdo_connect(envv('WEBSP_USER', 'websp'), envv('WEBSP_PASSWORD', 'websp_pass'));
}

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function render_rows(array $rows): void
{
    if (!$rows) {
        echo '<p>No rows.</p>';
        return;
    }
    echo '<table><thead><tr>';
    foreach (array_keys($rows[0]) as $key) {
        echo '<th>' . h($key) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($row as $value) {
            echo '<td>' . h($value) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table>';
}

function page_header(string $title): void
{
    echo '<!doctype html><html lang="zh-Hant"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . '</title><style>';
    echo 'body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;max-width:980px;margin:32px auto;padding:0 16px;line-height:1.55}';
    echo 'nav a{margin-right:12px} table{border-collapse:collapse;width:100%;margin-top:16px} th,td{border:1px solid #ccc;padding:8px;text-align:left}';
    echo 'code,pre{background:#f3f3f3;padding:2px 5px} pre{padding:12px;overflow:auto}.ok{color:#146c2e}.fail{color:#a11111}.warn{color:#8a5a00}';
    echo 'input{padding:8px;margin:4px 0} button{padding:8px 12px}.box{border:1px solid #ccc;padding:12px;margin:12px 0}';
    echo '</style></head><body><nav><a href="/">Home</a><a href="/setup.php">setup</a><a href="/permission-check.php">permission</a></nav>';
    echo '<h1>' . h($title) . '</h1>';
}

function page_footer(): void
{
    echo '</body></html>';
}
