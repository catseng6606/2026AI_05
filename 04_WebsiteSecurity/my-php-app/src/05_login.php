<?php
session_start();

// 處理登出
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: 05_login.php");
    exit();
}

// 模擬簡單的登入驗證
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['logout'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'alice' && $password === 'password123') {
        // 登入成功，將身分寫入 Session
        $_SESSION['user'] = 'alice';
        header("Location: 05_login.php");
        exit();
    } else {
        $error = "帳號或密碼錯誤。";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>銀行登入</title></head>
<body>
    <h2>銀行系統 - 用戶登入</h2>
    
    <?php if (isset($_SESSION['user'])): ?>
        <!-- 已登入狀態 -->
        <div style="background-color: #d4edda; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
            <h3>✓ 已登入</h3>
            <p>歡迎，<strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong>！</p>
            
            <form method="POST" action="05_login.php" style="display: inline;">
                <button type="submit" name="logout" value="1" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;">
                    登出
                </button>
            </form>
        </div>
    <?php else: ?>
        <!-- 登入表單 -->
        <?php if (isset($error)): ?>
            <div style="background-color: #f8d7da; padding: 10px; border-radius: 3px; color: #721c24; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="05_login.php">
            帳號: <input type="text" name="username" required><br><br>
            密碼: <input type="password" name="password" required><br><br>
            <button type="submit">登入</button>
        </form>
        
        <p><strong>測試帳號：</strong></p>
        <ul>
            <li>帳號: alice</li>
            <li>密碼: password123</li>
        </ul>
    <?php endif; ?>
</body>
</html>