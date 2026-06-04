<?php
session_start();

// 檢查使用者是否已經登入（有沒有 Session Cookie）
if (!isset($_SESSION['user'])) {
    die("請先登入！");
}

// ❌ 漏洞點：直接接收 GET 參數進行敏感操作
$to = $_GET['to'] ?? '';
$amount = $_GET['amount'] ?? '';

if ($to && $amount) {
    // 模擬轉帳邏輯
    echo "【系統通知】轉帳成功！<br>";
    echo "已成功從 " . $_SESSION['user'] . " 的帳戶，轉出 $" . htmlspecialchars($amount) . " 元給帳號：" . htmlspecialchars($to);
} else {
    echo "請輸入完整的轉帳資訊。";
}
?>