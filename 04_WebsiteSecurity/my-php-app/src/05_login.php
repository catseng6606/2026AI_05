<?php
session_start();

// 模擬簡單的登入驗證
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'alice' && $password === 'password123') {
        // 登入成功，將身分寫入 Session
        $_SESSION['user'] = 'alice';
        echo "登入成功！歡迎 Alice。";
    } else {
        echo "帳號或密碼錯誤。";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>銀行登入</title></head>
<body>
    <h2>銀行系統 - 用戶登入</h2>
    <form method="POST" action="login.php">
        帳號: <input type="text" name="username"><br><br>
        密碼: <input type="password" name="password"><br><br>
        <button type="submit">登入</button>
    </form>
</body>
</html>