
MODE LOW_TOKEN
請一次建立完整 Docker Lab。只輸出必要說明；不要長篇解釋；完成後只列檔案清單與啟動/測試指令。若修改檔案，採 only diff 思維，不重複貼整份內容。不要問問題，依規格直接產生。

目標：
建立 PHP 8.2 + MySQL 8.4 + Adminer + sqlmap 教學環境，用於 SQL Injection、PDO Prepared Statement、Stored Procedure、MySQL 最小權限、sqlmap 驗證教學。

檔案結構：
```
.
├── .env
├── Dockerfile
├── docker-compose.yml
├── README.md
├── mysql/
│   ├── 01_user_crud_sp.sql
│   ├── 02_websp.sql
│   └── 03_sample_data.sql
├── requests/
│   ├── unsafe-post.req
│   ├── safe-post.req
│   └── callsp-post.req
├── scripts/
│   ├── sqlmap-unsafe.sh
│   ├── sqlmap-safe.sh
│   ├── sqlmap-callsp.sh
│   ├── sqlmap-sp-unsafe.sh
│   ├── sqlmap-post-unsafe.sh
│   └── test-websp-permission.sh
└── src/
    ├── index.php
    ├── db.php
    ├── unsafe.php
    ├── unsafe-post.php
    ├── safe.php
    ├── safe-post.php
    ├── callsp.php
    ├── sp-unsafe.php
    └── style.css
```
.env：

MYSQL_ROOT_PASSWORD=rootpass
MYSQL_DATABASE=sqli_lab
MYSQL_APP_USER=labuser
MYSQL_APP_PASSWORD=labpass
MYSQL_SP_OWNER=sp_owner
MYSQL_SP_OWNER_PASSWORD=sp_owner_pass
MYSQL_WEBSP_USER=websp
MYSQL_WEBSP_PASSWORD=websp_pass
DB_HOST=mysql
DB_NAME=sqli_lab
DB_USER=websp
DB_PASS=websp_pass
PHP_PORT=8080
ADMINER_PORT=8081

docker-compose.yml：
- php
  - build: .
  - ports: ${PHP_PORT}:80
  - volumes: ./src:/var/www/html
  - environment: DB_HOST, DB_NAME, DB_USER, DB_PASS
  - depends_on: mysql
- mysql
  - image: mysql:8.4
  - command: --character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci
  - environment 使用 .env
  - volumes:
    - mysql_data:/var/lib/mysql
    - ./mysql:/docker-entrypoint-initdb.d
- adminer
  - image: adminer
  - ports: ${ADMINER_PORT}:8080
  - depends_on: mysql
- volumes: mysql_data

Dockerfile：
FROM php:8.2-apache
安裝 pdo_mysql、mysqli
可啟用 apache rewrite
不要 CDN

src/db.php：
- PDO
- env: DB_HOST, DB_NAME, DB_USER, DB_PASS
- charset=utf8mb4
- ERRMODE_EXCEPTION
- FETCH_ASSOC
- ATTR_EMULATE_PREPARES=false

src/index.php：
教學首頁，列連結：
- unsafe.php?id=1
- unsafe-post.php
- safe.php?id=1
- safe-post.php
- callsp.php?action=list
- callsp.php?action=get&id=1
- sp-unsafe.php?keyword=a
- Adminer: http://localhost:8081
- sqlmap 指令摘要
- 免責聲明：僅限本機 Docker Lab，禁止未授權測試

src/unsafe.php：
GET id，故意 SQL 字串串接：

$id = $_GET['id'] ?? '1';
$sql = "SELECT id, username, email, role, created_at FROM users WHERE id = $id";
$stmt = $pdo->query($sql);

頁面顯示 SQL、結果、危險提示。

src/unsafe-post.php：
POST keyword，故意 LIKE 字串串接：

$keyword = $_POST['keyword'] ?? '';
$sql = "SELECT id, username, email, role FROM users WHERE username LIKE '%$keyword%'";

提供 HTML form。
讓 sqlmap 可用 request 檔測 POST SQLi。

src/safe.php：
GET id，PDO Prepared Statement：

$id = (int)($_GET['id'] ?? 1);
$stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);

src/safe-post.php：
POST keyword，PDO Prepared Statement：

$stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE username LIKE :keyword");
$stmt->execute([':keyword' => '%' . $keyword . '%']);

src/callsp.php：
使用 PDO prepared statement 呼叫 SP。
支援：
- action=list => CALL sp_user_list()
- action=get&id=1 => CALL sp_user_get(:id)
- action=create&username=x&email=x@example.test&role=student => CALL sp_user_create(...)
- action=update&id=1&username=x&email=x@example.test&role=student => CALL sp_user_update(...)
- action=delete&id=1 => CALL sp_user_delete(:id)

不得字串串接參數。

src/sp-unsafe.php：
示範「SP 不等於安全」。
呼叫故意不安全 SP：

CALL sp_user_search_unsafe(:keyword)

頁面說明：
- PHP 呼叫雖然用 prepared statement
- 但 SP 內部用 CONCAT 組動態 SQL
- 所以仍可能 SQLi

mysql/01_user_crud_sp.sql：
先 USE sqli_lab;

建立 users table：

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'student',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

建立 sp_owner：

CREATE USER IF NOT EXISTS 'sp_owner'@'%' IDENTIFIED BY 'sp_owner_pass';
GRANT SELECT, INSERT, UPDATE, DELETE ON sqli_lab.users TO 'sp_owner'@'%';

建立 SP，全部使用：

CREATE DEFINER = 'sp_owner'@'%'
PROCEDURE ...
SQL SECURITY DEFINER

SP：
- sp_user_list()
- sp_user_get(IN p_id INT)
- sp_user_create(IN p_username VARCHAR(50), IN p_email VARCHAR(100), IN p_role VARCHAR(20))
- sp_user_update(IN p_id INT, IN p_username VARCHAR(50), IN p_email VARCHAR(100), IN p_role VARCHAR(20))
- sp_user_delete(IN p_id INT)

安全 SP 不得使用 CONCAT 動態 SQL。

額外建立故意不安全 SP：

sp_user_search_unsafe(IN p_keyword VARCHAR(100))

內容故意使用：
SET @sql = CONCAT("SELECT id, username, email, role FROM users WHERE username LIKE '%", p_keyword, "%'");
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

加註 SQL COMMENT 說明：此 SP 故意不安全，僅供教學。

mysql/02_websp.sql：
USE sqli_lab;

CREATE USER IF NOT EXISTS 'websp'@'%' IDENTIFIED BY 'websp_pass';

只授權 EXECUTE：

GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_list TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_get TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_create TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_update TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_delete TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_search_unsafe TO 'websp'@'%';

不得授權 websp table SELECT/INSERT/UPDATE/DELETE。
FLUSH PRIVILEGES;

mysql/03_sample_data.sql：
插入假資料：
- alice / alice@example.test / student
- bob / bob@example.test / teacher
- charlie / charlie@example.test / student
- admin_demo / admin@example.test / admin
- test01 / test01@example.test / student
不得使用真實個資。

requests/unsafe-post.req：
完整 HTTP request，目標 localhost:8080/unsafe-post.php，POST keyword=a。

requests/safe-post.req：
完整 HTTP request，目標 localhost:8080/safe-post.php，POST keyword=a。

requests/callsp-post.req：
若 callsp 僅 GET，可放註解或建立 POST 範例；sqlmap 可用 -r 測試。

scripts/sqlmap-unsafe.sh：
#!/usr/bin/env bash
set -e
sqlmap -u "http://localhost:8080/unsafe.php?id=1" -p id --batch
sqlmap -u "http://localhost:8080/unsafe.php?id=1" -p id --dbs --batch
sqlmap -u "http://localhost:8080/unsafe.php?id=1" -p id -D sqli_lab --tables --batch
sqlmap -u "http://localhost:8080/unsafe.php?id=1" -p id -D sqli_lab -T users --columns --batch
sqlmap -u "http://localhost:8080/unsafe.php?id=1" -p id -D sqli_lab -T users --dump --batch

scripts/sqlmap-safe.sh：
#!/usr/bin/env bash
set -e
sqlmap -u "http://localhost:8080/safe.php?id=1" -p id --batch

scripts/sqlmap-callsp.sh：
#!/usr/bin/env bash
set -e
sqlmap -u "http://localhost:8080/callsp.php?action=get&id=1" -p id --batch

scripts/sqlmap-sp-unsafe.sh：
#!/usr/bin/env bash
set -e
sqlmap -u "http://localhost:8080/sp-unsafe.php?keyword=a" -p keyword --batch

scripts/sqlmap-post-unsafe.sh：
#!/usr/bin/env bash
set -e
sqlmap -r requests/unsafe-post.req -p keyword --batch
sqlmap -r requests/unsafe-post.req -p keyword -D sqli_lab -T users --dump --batch

scripts/test-websp-permission.sh：
#!/usr/bin/env bash
set -e
echo "Expect FAIL: websp direct SELECT"
docker compose exec mysql mysql -uwebsp -pwebsp_pass sqli_lab -e "SELECT * FROM users;" || true
echo "Expect OK: websp CALL SP"
docker compose exec mysql mysql -uwebsp -pwebsp_pass sqli_lab -e "CALL sp_user_list();"

chmod +x scripts/*.sh

README.md：
繁體中文，簡潔，包含：
1. 專案目的
2. 架構：

PHP 8.2 Apache
  ↓ PDO
MySQL 8.4
  ↑
Adminer

3. 啟動：

docker compose up -d --build

4. 停止：

docker compose down

5. 重建 DB：

docker compose down -v
docker compose up -d --build

6. 網址：
PHP: http://localhost:8080
Adminer: http://localhost:8081

7. Adminer 登入：
root:
Server mysql
Username root
Password rootpass
Database sqli_lab

websp:
Server mysql
Username websp
Password websp_pass
Database sqli_lab

8. 教學流程：
A unsafe.php
B sqlmap unsafe
C safe.php
D sqlmap safe
E callsp.php
F sp-unsafe.php 說明 SP 內動態 SQL 仍危險
G Adminer 用 websp 登入測 SELECT 失敗、CALL 成功
H scripts/test-websp-permission.sh

9. sqlmap：
./scripts/sqlmap-unsafe.sh
./scripts/sqlmap-safe.sh
./scripts/sqlmap-callsp.sh
./scripts/sqlmap-sp-unsafe.sh
./scripts/sqlmap-post-unsafe.sh

10. 權限模型：
- root: 初始化
- sp_owner: SP DEFINER，有 users CRUD
- websp: PHP 使用，只能 EXECUTE 指定 SP，不能直接 CRUD table

11. DEFINER / SQL SECURITY DEFINER：
- SP 執行時用 DEFINER 權限
- websp 只要 EXECUTE
- 不要 root 當 DEFINER
- sp_owner 只給必要權限

12. 安全提醒：
- 僅限本機 Docker Lab
- 禁止掃描未授權網站
- 正式環境不要公開 Adminer
- 正式環境需防火牆/VPN/Log/備份/最小權限

13. 教學總結：
Prepared Statement 防止輸入變 SQL 指令。
Stored Procedure 封裝資料操作，但不是自動安全。
最小權限降低漏洞發生後的傷害。
sqlmap 用於驗證防護，不是攻擊未授權目標。

風格：
- 簡單 PHP，無框架
- 簡單 HTML/CSS
- 不用 CDN
- 每頁顯示教學免責提醒
- 假資料 only
- 不要產生大型依賴
- 不要使用 composer
- 完成後執行：
  docker compose config
  chmod +x scripts/*.sh

最後輸出：
- 檔案清單
- docker compose up -d --build
- unsafe 測試指令
- safe 測試指令
- websp 權限測試指令
- 不要輸出完整檔案內容

