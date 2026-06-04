<!DOCTYPE html>
<html>
<head><title>DOM XSS 範例（jQuery 版）</title></head>
<body>
    <div id="greeting"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // 1. 前端 JavaScript 直接從網址列抓取 hash 值 (例如 #name=Tom)
        const hashParams = new URLSearchParams(window.location.hash.substring(1));
        const name = hashParams.get('name');

        if (name) {
            
            // ❌ 2. 危險！使用 html() 會把字串當成 HTML 標籤解析並執行
            // $('#greeting').html("哈囉，" + name);
            // ✅ 2. 安全！使用 text() 只會將字串當成純文字處理
            // $('#greeting').text() 會自動將輸入當成純文字處理，不會解析 HTML 標籤
            $('#greeting').text("哈囉，" + name);
        }
    </script>
</body>
</html>