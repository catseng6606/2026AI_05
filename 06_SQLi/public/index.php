<?php
require __DIR__ . '/config.php';
page_header('SQL Injection Lab');
?>
<p>教學用 Docker Lab：unsafe SQL、PDO Prepared Statement、Stored Procedure、SQL SECURITY DEFINER、最小權限與 sqlmap。</p>
<ul>
  <li><a href="/setup.php">setup.php</a> - 初始化 database、users、SP、grants</li>
  <li><a href="/unsafe.php?id=1">unsafe.php</a> - GET SQL Injection，使用 webtable</li>
  <li><a href="/post-unsafe.php">post-unsafe.php</a> - POST SQL Injection，使用 webtable</li>
  <li><a href="/safe-pdo.php?id=1">safe-pdo.php</a> - PDO Prepared Statement，使用 webtable</li>
  <li><a href="/callsp.php?id=1&keyword=a">callsp.php</a> - 安全 Stored Procedure，使用 websp</li>
  <li><a href="/sp-unsafe.php?keyword=a">sp-unsafe.php</a> - SP 內動態 SQL Injection，使用 websp</li>
  <li><a href="/permission-check.php">permission-check.php</a> - webtable / websp 權限檢查</li>
</ul>
<h2>角色</h2>
<ul>
  <li><code>root</code>：只給 setup.php 初始化使用。</li>
  <li><code>sp_owner</code>：Stored Procedure DEFINER，不給 PHP Lab 使用。</li>
  <li><code>webtable</code>：直接 CRUD users，不可 CALL SP。</li>
  <li><code>websp</code>：不可直接 CRUD users，只能 EXECUTE 指定 SP。</li>
</ul>
<?php page_footer(); ?>
