<?php
declare(strict_types=1);

$env = parse_ini_file(__DIR__ . '/../.env');

function root_connect(): mysqli
{
    global $env;
    $conn = new mysqli(
        $env['DB_HOST'],
        'root',
        $env['MYSQL_ROOT_PASSWORD'],
        $env['MYSQL_DATABASE']
    );
    if ($conn->connect_error) {
        die("[FAIL] root connection: " . $conn->connect_error);
    }
    return $conn;
}

function webtable_connect(): mysqli
{
    global $env;
    $conn = new mysqli(
        $env['DB_HOST'],
        $env['WEBTABLE_USER'],
        $env['WEBTABLE_PASSWORD'],
        $env['MYSQL_DATABASE']
    );
    if ($conn->connect_error) {
        die("[FAIL] webtable connection: " . $conn->connect_error);
    }
    return $conn;
}

function websp_connect(): mysqli
{
    global $env;
    $conn = new mysqli(
        $env['DB_HOST'],
        $env['WEBSP_USER'],
        $env['WEBSP_PASSWORD'],
        $env['MYSQL_DATABASE']
    );
    if ($conn->connect_error) {
        die("[FAIL] websp connection: " . $conn->connect_error);
    }
    return $conn;
}
