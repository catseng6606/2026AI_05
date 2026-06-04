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
            // ✅ 2. 安全！使用 textContent 只會將字串當成純文字處理
            document.getElementById('greeting').textContent = "哈囉，" + name;
        }
    </script>
</body>
</html>