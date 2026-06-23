<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SQL Injection Lab</title>
<style>
body { font-family: monospace; max-width: 800px; margin: 40px auto; padding: 0 20px; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
th { background: #eee; }
.warn { background: #fff3cd; }
.danger { background: #f8d7da; }
.safe { background: #d4edda; }
.info { background: #d1ecf1; }
</style>
</head>
<body>
<h1>SQL Injection Lab</h1>
<p><strong>注意：</strong> 本 Lab 僅限本機教學使用，禁止掃描未授權網站。</p>
<table>
<thead>
<tr><th>頁面</th><th>DB 帳號</th><th>安全性</th><th>教學重點</th></tr>
</thead>
<tbody>
<tr><td><a href="setup.php">setup.php</a></td><td>root</td><td class="info">初始化</td><td>初始化環境</td></tr>
<tr><td><a href="unsafe.php?id=1">unsafe.php</a></td><td>webtable</td><td class="danger">不安全</td><td>GET SQL Injection, 字串串接</td></tr>
<tr><td><a href="post-unsafe.php">post-unsafe.php</a></td><td>webtable</td><td class="danger">不安全</td><td>POST SQL Injection, 字串串接</td></tr>
<tr><td><a href="safe-pdo.php?id=1">safe-pdo.php</a></td><td>webtable</td><td class="safe">安全</td><td>PDO Prepared Statement</td></tr>
<tr><td><a href="callsp.php?id=1">callsp.php</a></td><td>websp</td><td class="safe">安全</td><td>Stored Procedure + 最小權限</td></tr>
<tr><td><a href="sp-unsafe.php?keyword=">sp-unsafe.php</a></td><td>websp</td><td class="danger">不安全</td><td>SP 內動態 SQL Injection</td></tr>
<tr><td><a href="permission-check.php">permission-check.php</a></td><td>both</td><td class="info">驗證</td><td>權限模型驗證</td></tr>
</tbody>
</table>
</body>
</html>
