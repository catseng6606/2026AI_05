# SQL Injection Lab

## 專案目的

本 Lab 用於示範 SQL Injection 攻擊、PDO Prepared Statement 防護、Stored Procedure 封裝資料操作、MySQL 最小權限、SP DEFINER / SQL SECURITY DEFINER、sqlmap 驗證，以及 Stored Procedure 不是自動安全的概念。

## 架構圖

```
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

## 檔案結構

```
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

## 啟動 / 停止 / 重建

```bash
# 啟動
docker compose up -d --build

# 停止
docker compose down

# 重建 DB（清除 volume）
docker compose down -v && docker compose up -d --build
```

## 初始化

開啟 http://localhost:8080/setup.php 完成 database、table、users、stored procedures、grants 初始化。可重複執行。

## Adminer

開啟 http://localhost:8081

### root

```
Server: mysql
Username: root
Password: rootpass
Database: sqli_lab
```

### sp_owner

```
Server: mysql
Username: sp_owner
Password: sp_owner_pass
Database: sqli_lab
```

此帳號只作為 SP DEFINER 教學觀察用，不給 PHP Lab 頁面使用。

### webtable

```
Server: mysql
Username: webtable
Password: webtable_pass
Database: sqli_lab
```

可直接 CRUD users，但不可 CALL SP。

### websp

```
Server: mysql
Username: websp
Password: websp_pass
Database: sqli_lab
```

不可直接 CRUD users，只能 CALL 明確授權的 SP。

## Lab 頁面

| 頁面 | 說明 |
|---|---|
| setup.php | 初始化環境 |
| unsafe.php | GET SQL Injection，使用 webtable |
| post-unsafe.php | POST SQL Injection，使用 webtable |
| safe-pdo.php | PDO Prepared Statement，使用 webtable |
| callsp.php | Stored Procedure + 最小權限，使用 websp |
| sp-unsafe.php | SP 內動態 SQL Injection，使用 websp |
| permission-check.php | 權限驗證 |

## sqlmap 測試

```bash
# 需安裝 sqlmap 後執行

# 1. 掃 unsafe.php（應找到 SQL Injection）
bash scripts/sqlmap-unsafe.sh

# 2. 掃 post-unsafe.php（應找到 SQL Injection）
bash scripts/sqlmap-post-unsafe.sh

# 3. 掃 safe-pdo.php（應找不到 SQL Injection）
bash scripts/sqlmap-safe-pdo.sh

# 4. 掃 callsp.php（安全 SP，應找不到）
bash scripts/sqlmap-callsp.sh

# 5. 掃 sp-unsafe.php（SP 內動態 SQL，應找到 SQL Injection）
bash scripts/sqlmap-sp-unsafe.sh
```

也可直接使用 request files：

```bash
sqlmap -r requests/unsafe.txt --batch --level=2 --risk=1
```

## 權限驗證

```bash
# 需安裝 mysql client 後執行
bash scripts/test-websp-permission.sh
```

## 教學流程

```
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

## 權限模型說明

### root

- 用途：初始化 database、建立 table、建立 users、建立 SP、授權
- 限制：只能用於 setup.php

### sp_owner

- 用途：Stored Procedure DEFINER
- 權限：對 users 有 SELECT/INSERT/UPDATE/DELETE + routine 管理
- 限制：不給 PHP 頁面使用

### webtable

- 用途：傳統 Web 直連 table 範例
- 權限：對 users 有 SELECT/INSERT/UPDATE/DELETE
- 限制：不可 CALL SP

### websp

- 用途：SP 封裝範例
- 權限：只能 CALL 已授權的 SP
- 限制：不可直接 CRUD users

## webtable vs websp

| 項目 | webtable | websp |
|---|---|---|
| 直接存取 users | 可 | 不可 |
| 執行 SP | 不可 | 可（僅已授權） |
| 風險 | SQL Injection 直達 table | 僅限 SP 暴露的操作 |

## DEFINER / SQL SECURITY DEFINER

所有 SP 使用 `SQL SECURITY DEFINER` 且 DEFINER 為 `sp_owner`，表示執行 SP 時以 sp_owner 的權限操作 users table。websp 雖然無直接 table 權限，但透過 CALL SP 間接獲得 sp_owner 授權。

## Stored Procedure 不是自動安全的

`sp_search_users_unsafe` 內部使用 `CONCAT` + `PREPARE` + `EXECUTE` 串接輸入參數，仍可能 SQL Injection。SP 僅是封裝層，不是安全保證。

## Prepared Statement

PDO Prepared Statement 將 SQL 語法與參數分離，防止輸入變成 SQL 指令。safe-pdo.php 示範此防護。

## 最小權限

- webtable 只能做 table CRUD，不能執行 SP
- websp 只能 CALL 特定 SP，不能直接存取 table
- 所有授權逐一設定，不使用 `GRANT EXECUTE ON sqli_lab.*`

## 密碼明文風險

`password_plain` 欄位是故意保留的風險欄位，用於教學「密碼明文」問題。正式環境禁止儲存明文密碼，應使用 `password_hash()` 等安全雜湊。

## 安全提醒

本專案僅限本機 Docker Lab 教學使用。
禁止掃描、測試、攻擊未授權網站。
正式環境不要公開 Adminer。
正式環境不要使用明文密碼。
正式環境不要將 root 密碼放進 Web 可讀設定。
正式環境不要使用 root 作為 SP DEFINER。
正式環境需搭配防火牆、VPN、Log、Backup、最小權限。
Stored Procedure 不是自動安全，內部若使用動態 SQL 串接輸入，仍可能 SQL Injection。

## 教學總結

- Prepared Statement：防止輸入變成 SQL 指令。
- Stored Procedure：封裝資料操作入口。
- SQL SECURITY DEFINER：讓 SP 用 sp_owner 權限執行。
- websp：只拿到被授權的 SP 按鈕，不能直接摸 table。
- webtable：傳統直連 table 帳號，權限較大。
- 最小權限：降低漏洞發生後的傷害。
- sqlmap：用於驗證防護，不是攻擊未授權目標。
