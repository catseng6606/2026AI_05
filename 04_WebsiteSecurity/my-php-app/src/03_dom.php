<!DOCTYPE html>
<html>
<head><title>DOM XSS 範例</title></head>
<body>
    <div id="greeting"></div>

    <script>
        // 1. 前端 JavaScript 直接從網址列抓取 hash 值 (例如 #name=Tom)
        const hashParams = new URLSearchParams(window.location.hash.substring(1));
        const name = hashParams.get('name');

        if (name) {
            // ❌ 2. 危險！使用 innerHTML 會把字串當成 HTML 標籤解析並執行
            document.getElementById('greeting').innerHTML = "哈囉，" + name;
        }
    </script>
</body>
</html>