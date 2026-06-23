MODE LOW_TOKEN

請一次建立完整 Docker Lab。只輸出必要說明；不要長篇解釋。完成後只列檔案清單與啟動/測試指令。若修改檔案，採 only diff 思維，不重複貼整份內容。不要問問題，依規格直接產生。

# SQL Injection Lab

## 目標

建立 PHP 8.2 + Apache + MySQL 8.4 + Adminer + sqlmap 教學環境，用於示範：

1. SQL Injection 攻擊
2. PDO Prepared Statement 防護
3. Stored Procedure 封裝資料操作
4. MySQL 最小權限
5. SP DEFINER / SQL SECURITY DEFINER
6. sqlmap 驗證
7. Stored Procedure 不是自動安全，SP 內動態 SQL 仍可能 SQL Injection

---

## 核心規則

1. Docker 啟動後，透過 `setup.php` 完成 database、table、users、stored procedures、grants 初始化。
2. `setup.php` 必須可重複執行，不可因 database、table、user、SP 已存在而失敗。
3. `setup.php` 使用 root 連線初始化。
4. 正式 Lab 頁面不得使用 root。
5. 不要用 root 當 Stored Procedure DEFINER。
6. `sp_owner` 只作為 Stored Procedure DEFINER，不給 PHP Lab 頁面使用。
7. SQL Injection unsafe lab 使用 `webtable`。
8. Prepared Statement safe lab 使用 `webtable`。
9. Stored Procedure safe lab 使用 `websp`。
10. `websp` 不可直接 CRUD table，只能 EXECUTE 明確授權的 Stored Procedures。
11. `websp` 必須逐一授權 SP，不可使用 `GRANT EXECUTE ON sqli_lab.*`。
12. `webtable` 可直接 CRUD `users` table，但不可 CALL SP。
13. 必須提供 `permission-check.php` 驗證權限模型。
14. 必須提供 `sp-unsafe.php` 示範 SP 內動態 SQL 仍可能 SQL Injection。
15. 所有程式與 README 必須明確標示：本 Lab 僅限本機教學，禁止掃描未授權網站。

---

## 檔案結構

```text
.
├── .env
├── Dockerfile
├── docker-compose.yml
├── README.md
├── public/
│   ├── index.php
│   ├── setup.php
│   ├── config.php
│   ├── unsafe.php
│   ├── post-unsafe.php
│   ├── safe-pdo.php
│   ├── callsp.php
│   ├── sp-unsafe.php
│   └── permission-check.php
├── mysql/
│   ├── 01_schema.sql
│   ├── 02_sample_data.sql
│   ├── 03_procedures.sql
│   └── 04_grants.sql
├── scripts/
│   ├── sqlmap-unsafe.sh
│   ├── sqlmap-post-unsafe.sh
│   ├── sqlmap-safe-pdo.sh
│   ├── sqlmap-callsp.sh
│   ├── sqlmap-sp-unsafe.sh
│   └── test-websp-permission.sh
└── requests/
    ├── unsafe.txt
    ├── post-unsafe.txt
    ├── safe-pdo.txt
    ├── callsp.txt
    └── sp-unsafe.txt
```

---

## Docker 規格

使用：

* PHP 8.2 Apache
* MySQL 8.4
* Adminer latest
* sqlmap 可用 container 或 scripts 方式執行

`docker-compose.yml`：

* PHP 對外：`http://localhost:8080`
* Adminer 對外：`http://localhost:8081`
* MySQL 僅 Docker network 內部使用，不對外暴露 host port
* PHP service 可連到 mysql service
* Adminer server 使用 `mysql`

---

## .env

請建立：

```env
MYSQL_ROOT_PASSWORD=rootpass
MYSQL_DATABASE=sqli_lab

DB_HOST=mysql
DB_NAME=sqli_lab

SP_OWNER_USER=sp_owner
SP_OWNER_PASSWORD=sp_owner_pass

WEBTABLE_USER=webtable
WEBTABLE_PASSWORD=webtable_pass

WEBSP_USER=websp
WEBSP_PASSWORD=websp_pass
```

---

## Database

Database：

```text
sqli_lab
```

Table：

```sql
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_plain VARCHAR(100) NOT NULL,
    role VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

注意：

`password_plain` 是故意保留的風險欄位，用於教學「密碼明文」問題。README 必須明確說明正式環境禁止儲存明文密碼，應使用安全雜湊。

---

## Sample Data

至少建立：

```text
admin
alice
bob
teacher
student
```

每筆資料包含：

* username
* email
* password_plain
* role

---

## 帳號與權限模型

### 1. root

用途：

* 初始化 database
* 建立 table
* 建立 users
* 建立 Stored Procedures
* 授權

限制：

* 只能用於 `setup.php`
* 其他 Lab 頁面不得使用 root

---

### 2. sp_owner

用途：

* Stored Procedure DEFINER

權限：

* 對 `sqli_lab.users` 有：

  * SELECT
  * INSERT
  * UPDATE
  * DELETE
* 有必要的 routine 建立與修改權限

限制：

* 不給 PHP Lab 頁面使用
* 不可作為 Web 連線帳號
* 不可用 root 取代

---

### 3. webtable

用途：

* 傳統 Web 直連 table 範例
* SQL Injection unsafe 範例
* PDO Prepared Statement safe 範例

權限：

* 對 `sqli_lab.users` 有：

  * SELECT
  * INSERT
  * UPDATE
  * DELETE

限制：

* 不授予 EXECUTE Stored Procedure 權限

預期：

```text
webtable SELECT users                    → OK
webtable INSERT users                    → OK
webtable UPDATE users                    → OK
webtable DELETE users                    → OK
webtable CALL sp_get_user_by_id          → FAIL
webtable CALL sp_search_users            → FAIL
webtable CALL sp_create_user             → FAIL
webtable CALL sp_search_users_unsafe     → FAIL
```

---

### 4. websp

用途：

* Stored Procedure 封裝範例
* 最小權限範例

權限：

* 不可直接 SELECT / INSERT / UPDATE / DELETE `users`
* 只能 EXECUTE 明確授權的 Stored Procedures

限制：

* 不可使用：

```sql
GRANT EXECUTE ON sqli_lab.* TO 'websp'@'%';
```

必須逐一授權：

```sql
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_get_user_by_id TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_search_users TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_create_user TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_search_users_unsafe TO 'websp'@'%';
```

預期：

```text
websp SELECT users                    → FAIL
websp INSERT users                    → FAIL
websp UPDATE users                    → FAIL
websp DELETE users                    → FAIL
websp CALL sp_get_user_by_id          → OK
websp CALL sp_search_users            → OK
websp CALL sp_create_user             → OK
websp CALL sp_search_users_unsafe     → OK
websp CALL 未授權 SP                  → FAIL
```

---

## Stored Procedures

所有 Stored Procedures 建立時必須使用：

```sql
SQL SECURITY DEFINER
```

且 DEFINER 必須是：

```sql
'sp_owner'@'%'
```

不要用 root 當 DEFINER。

---

### 安全 SP

請建立：

```text
sp_get_user_by_id(IN p_id INT)
```

用途：

* 依 id 查詢 user
* 不使用動態 SQL

---

```text
sp_search_users(IN p_keyword VARCHAR(100))
```

用途：

* 搜尋 username / email
* 不使用動態 SQL
* 使用安全參數處理

---

```text
sp_create_user(
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_password_plain VARCHAR(100),
    IN p_role VARCHAR(30)
)
```

用途：

* 新增 user
* 不使用動態 SQL

---

### 危險 SP

請建立：

```text
sp_search_users_unsafe(IN p_keyword VARCHAR(100))
```

要求：

* 內部故意使用 `CONCAT`
* 使用 `PREPARE`
* 使用 `EXECUTE`
* 故意讓它有 SQL Injection 風險
* 用來示範 Stored Procedure 不是自動安全

---

## setup.php

`setup.php` 必須：

1. 使用 root 連線 MySQL
2. 可重複執行
3. 建立 database
4. 建立 users table
5. 匯入 sample data
6. 建立 MySQL users：

   * sp_owner
   * webtable
   * websp
7. 建立 Stored Procedures
8. 授權
9. 執行權限檢查
10. 顯示每一步 OK / FAIL

畫面至少顯示：

```text
[OK] connected as root
[OK] database created or exists
[OK] users table created or exists
[OK] sample data inserted or refreshed
[OK] sp_owner created or exists
[OK] webtable created or exists
[OK] websp created or exists
[OK] procedures created
[OK] grants applied
[OK] permission check completed
```

若失敗需顯示錯誤訊息，方便教學除錯。

---

## public/config.php

請提供簡單 DB 連線設定。

至少支援三種連線：

```text
root setup connection
webtable connection
websp connection
```

其他 Lab 頁面不得使用 root connection。

---

## public/index.php

首頁需列出所有 Lab 入口。

每個入口需顯示：

* 頁面名稱
* 使用 DB 帳號
* 是否故意不安全
* 教學重點

至少包含：

```text
setup.php              初始化環境
unsafe.php             GET SQL Injection，使用 webtable
post-unsafe.php        POST SQL Injection，使用 webtable
safe-pdo.php           PDO Prepared Statement，使用 webtable
callsp.php             Stored Procedure + 最小權限，使用 websp
sp-unsafe.php          SP 內動態 SQL Injection，使用 websp
permission-check.php   權限驗證
```

---

## unsafe.php

要求：

* 使用 `webtable`
* 使用 GET 參數：

```text
id
```

* 故意使用 SQL 字串串接
* 可被 SQL Injection
* 顯示實際 SQL 語句，方便教學
* 預設提供範例連結：

```text
?id=1
?id=1 OR 1=1
```

---

## post-unsafe.php

要求：

* 使用 `webtable`
* 使用 POST 參數：

```text
username
```

* 故意使用 SQL 字串串接
* 可被 SQL Injection
* 提供 HTML form
* 顯示實際 SQL 語句，方便教學

---

## safe-pdo.php

要求：

* 使用 `webtable`
* 使用 PDO Prepared Statement
* 不可用字串串接 SQL
* 使用 GET 參數：

```text
id
```

* sqlmap 應掃不到 SQL Injection
* 頁面需說明：

  * 此頁仍然直連 table
  * 但透過 Prepared Statement 防止 SQL Injection
  * 這是語法層防護，不是權限層防護

---

## callsp.php

要求：

* 使用 `websp`
* 呼叫安全 SP：

  * `sp_get_user_by_id`
  * `sp_search_users`
* 不直接查詢 `users`
* 顯示：

  * websp 無 table CRUD 權限
  * websp 只能 CALL 已授權 SP
  * SP 透過 `SQL SECURITY DEFINER` 使用 `sp_owner` 權限執行

---

## sp-unsafe.php

要求：

* 使用 `websp`
* 呼叫危險 SP：

```text
sp_search_users_unsafe
```

* 用來示範：

  * Stored Procedure 不是自動安全
  * 如果 SP 內部用動態 SQL 串接輸入，仍可能 SQL Injection
* 頁面需顯示警告文字

---

## permission-check.php

必須測試並表格顯示：

### websp

```text
SELECT users                    → FAIL，符合預期
INSERT users                    → FAIL，符合預期
UPDATE users                    → FAIL，符合預期
DELETE users                    → FAIL，符合預期
CALL sp_get_user_by_id          → OK
CALL sp_search_users            → OK
CALL sp_create_user             → OK
CALL sp_search_users_unsafe     → OK
CALL 未授權 SP                  → FAIL，符合預期
```

### webtable

```text
SELECT users                    → OK
INSERT users                    → OK
UPDATE users                    → OK
DELETE users                    → OK
CALL sp_get_user_by_id          → FAIL，符合預期
CALL sp_search_users            → FAIL，符合預期
CALL sp_create_user             → FAIL，符合預期
CALL sp_search_users_unsafe     → FAIL，符合預期
```

---

## sqlmap scripts

建立以下 scripts，且必須可直接執行：

```text
scripts/sqlmap-unsafe.sh
scripts/sqlmap-post-unsafe.sh
scripts/sqlmap-safe-pdo.sh
scripts/sqlmap-callsp.sh
scripts/sqlmap-sp-unsafe.sh
```

每個 script 使用：

```text
--batch
--level=2
--risk=1
```

URL 使用：

```text
http://localhost:8080/...
```

POST 測試需正確使用：

```text
--data
```

---

## requests

建立 sqlmap request files：

```text
requests/unsafe.txt
requests/post-unsafe.txt
requests/safe-pdo.txt
requests/callsp.txt
requests/sp-unsafe.txt
```

內容需可被 sqlmap `-r` 使用。

---

## scripts/test-websp-permission.sh

要求：

* 可在本機執行
* 驗證 websp 不能直接 SELECT users
* 驗證 websp 可以 CALL 已授權 SP
* 驗證 webtable 可以 SELECT users
* 驗證 webtable 不能 CALL SP

---

## README.md

README 必須包含：

1. 專案目的
2. 架構圖
3. 檔案結構
4. 啟動指令
5. 停止指令
6. 重建 DB 指令
7. setup.php 初始化方式
8. Adminer 登入資訊
9. 每個 Lab 頁面的用途
10. sqlmap 測試流程
11. 權限模型說明
12. `webtable` vs `websp` 對比
13. DEFINER / SQL SECURITY DEFINER 說明
14. Stored Procedure 不是自動安全的說明
15. Prepared Statement 說明
16. 最小權限說明
17. 密碼明文風險說明
18. 安全提醒

---

## README 架構圖

請放入簡單文字圖：

```text
Browser
  ↓
PHP 8.2 Apache
  ├─ unsafe.php / post-unsafe.php / safe-pdo.php
  │    ↓ webtable
  │    ↓ direct CRUD users
  │
  └─ callsp.php / sp-unsafe.php
       ↓ websp
       ↓ CALL only
       ↓ Stored Procedure
       ↓ SQL SECURITY DEFINER
       ↓ sp_owner
       ↓ CRUD users

Adminer
  ↓
MySQL 8.4
```

---

## README 教學流程

請包含：

```text
A. docker compose up -d --build
B. 開啟 http://localhost:8080/setup.php 初始化
C. 開啟 unsafe.php 示範 GET SQL Injection
D. 使用 sqlmap 掃 unsafe.php
E. 開啟 post-unsafe.php 示範 POST SQL Injection
F. 使用 sqlmap 掃 post-unsafe.php
G. 開啟 safe-pdo.php 示範 Prepared Statement
H. 使用 sqlmap 掃 safe-pdo.php，應找不到 SQL Injection
I. 開啟 callsp.php 示範 Stored Procedure + 最小權限
J. 開啟 permission-check.php 驗證 websp / webtable 權限差異
K. 開啟 sp-unsafe.php 示範 SP 內動態 SQL 仍可能 SQL Injection
L. 使用 sqlmap 掃 sp-unsafe.php
```

---

## Adminer 登入資訊

README 需提供：

### root

```text
Server: mysql
Username: root
Password: rootpass
Database: sqli_lab
```

### sp_owner

```text
Server: mysql
Username: sp_owner
Password: sp_owner_pass
Database: sqli_lab
```

說明：

```text
此帳號只作為 SP DEFINER 教學觀察用，不給 PHP Lab 頁面使用。
```

### webtable

```text
Server: mysql
Username: webtable
Password: webtable_pass
Database: sqli_lab
```

說明：

```text
可直接 CRUD users，但不可 CALL SP。
```

### websp

```text
Server: mysql
Username: websp
Password: websp_pass
Database: sqli_lab
```

說明：

```text
不可直接 CRUD users，只能 CALL 明確授權的 SP。
```

---

## 安全提醒

README 必須明確寫：

```text
本專案僅限本機 Docker Lab 教學使用。
禁止掃描、測試、攻擊未授權網站。
正式環境不要公開 Adminer。
正式環境不要使用明文密碼。
正式環境不要將 root 密碼放進 Web 可讀設定。
正式環境不要使用 root 作為 SP DEFINER。
正式環境需搭配防火牆、VPN、Log、Backup、最小權限。
Stored Procedure 不是自動安全，內部若使用動態 SQL 串接輸入，仍可能 SQL Injection。
```

---

## 教學總結

README 最後請整理：

```text
Prepared Statement：防止輸入變成 SQL 指令。
Stored Procedure：封裝資料操作入口。
SQL SECURITY DEFINER：讓 SP 用 sp_owner 權限執行。
websp：只拿到被授權的 SP 按鈕，不能直接摸 table。
webtable：傳統直連 table 帳號，權限較大。
最小權限：降低漏洞發生後的傷害。
sqlmap：用於驗證防護，不是攻擊未授權目標。
```

---

## 輸出要求

請直接產生完整檔案。

完成後只列：

1. 檔案清單
2. 啟動指令
3. 初始化網址
4. sqlmap 測試指令
5. 權限驗證指令

不要輸出長篇教學說明。
不要問問題。
