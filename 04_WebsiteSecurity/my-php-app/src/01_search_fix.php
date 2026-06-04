<?php
// 後端完全沒過濾，直接接收 keyword 參數並印出
$keyword = $_GET['keyword'] ?? '';

// http://example.com/search.php?keyword=<script>alert('您的 Cookie 被偷囉：' + document.cookie);</script>
?>

<!DOCTYPE html>
<html>
<head><title>搜尋結果</title></head>
<body>
    <h2>您搜尋的關鍵字是：<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?></h2>
</body>
</html>