<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>SQL Injection Lab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SQL Injection Lab</h1>
        <div class="warning">僅限本機 Docker Lab，禁止未授權測試</div>
        <h2>教學頁面</h2>
        <ul>
            <li><a href="unsafe.php?id=1">unsafe.php?id=1</a> - SQL Injection (GET)</li>
            <li><a href="unsafe-post.php">unsafe-post.php</a> - SQL Injection (POST)</li>
            <li><a href="safe.php?id=1">safe.php?id=1</a> - PDO Prepared Statement (GET)</li>
            <li><a href="safe-post.php">safe-post.php</a> - PDO Prepared Statement (POST)</li>
            <li><a href="callsp.php?action=list">callsp.php?action=list</a> - Stored Procedure (Safe)</li>
            <li><a href="callsp.php?action=get&id=1">callsp.php?action=get&id=1</a> - SP Get User</li>
            <li><a href="sp-unsafe.php?keyword=a">sp-unsafe.php?keyword=a</a> - SP Unsafe Demo</li>
            <li><a href="http://localhost:8081">Adminer</a></li>
        </ul>
        <h2>sqlmap 指令摘要</h2>
        <pre>
./scripts/sqlmap-unsafe.sh
./scripts/sqlmap-safe.sh
./scripts/sqlmap-callsp.sh
./scripts/sqlmap-sp-unsafe.sh
./scripts/sqlmap-post-unsafe.sh
        </pre>
        <div class="footer">SQL Injection Teaching Lab - Docker 環境</div>
    </div>
</body>
</html>
