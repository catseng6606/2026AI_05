# SQL Injection Lab

教學用 Docker Lab，示範 SQL Injection、PDO Prepared Statement、Stored Procedure、`SQL SECURITY DEFINER`、最小權限與 sqlmap 驗證。

## Quick Start

```sh
docker compose up -d --build
```

開啟：

- Web: http://localhost:8080
- Setup: http://localhost:8080/setup.php
- Adminer: http://localhost:8081

第一次啟動後先執行 `setup.php`，它會用 root 建立 database、table、sample data、MySQL users、stored procedures 與 grants。

## Structure

```text
.
|-- .env
|-- Dockerfile
|-- docker-compose.yml
|-- README.md
|-- public/
|   |-- index.php
|   |-- setup.php
|   |-- config.php
|   |-- unsafe.php
|   |-- post-unsafe.php
|   |-- safe-pdo.php
|   |-- callsp.php
|   |-- sp-unsafe.php
|   `-- permission-check.php
|-- mysql/
|   |-- 01_schema.sql
|   |-- 02_sample_data.sql
|   |-- 03_procedures.sql
|   `-- 04_grants.sql
|-- scripts/
|   |-- sqlmap-unsafe.sh
|   |-- sqlmap-post-unsafe.sh
|   |-- sqlmap-safe-pdo.sh
|   |-- sqlmap-callsp.sh
|   |-- sqlmap-sp-unsafe.sh
|   `-- test-websp-permission.sh
`-- requests/
    |-- unsafe.txt
    |-- post-unsafe.txt
    |-- safe-pdo.txt
    |-- callsp.txt
    `-- sp-unsafe.txt
```

## Accounts

Adminer 登入：

| User | Password | Purpose |
| --- | --- | --- |
| root | rootpass | 初始化與管理，不給 Lab 頁面使用 |
| sp_owner | sp_owner_pass | Stored Procedure DEFINER |
| webtable | webtable_pass | 直接 CRUD `users`，不可 CALL SP |
| websp | websp_pass | 不可直接 CRUD `users`，只能 CALL 指定 SP |

Adminer Server 使用 `mysql`，Database 使用 `sqli_lab`。

## Lab Flow

1. `setup.php` 初始化環境。
2. `unsafe.php?id=1 OR 1=1` 示範 GET SQL Injection。
3. `post-unsafe.php` 輸入 `' OR '1'='1` 示範 POST SQL Injection。
4. `safe-pdo.php?id=1 OR 1=1` 示範 Prepared Statement 阻擋注入。
5. `callsp.php` 示範 `websp` 不能直接查 table，但可 CALL 安全 SP。
6. `permission-check.php` 檢查 `webtable` 與 `websp` 權限。
7. `sp-unsafe.php?keyword=' OR 1=1 -- ` 示範 SP 內動態 SQL 仍可被注入。

## sqlmap

啟動服務後執行：

```sh
sh scripts/sqlmap-unsafe.sh
sh scripts/sqlmap-post-unsafe.sh
sh scripts/sqlmap-safe-pdo.sh
sh scripts/sqlmap-callsp.sh
sh scripts/sqlmap-sp-unsafe.sh
```

或使用 request file：

```sh
docker compose run --rm sqlmap -r /work/requests/unsafe.txt --batch --level=2 --risk=1
```

目前 `sqlmap` service 使用 host network。若 Docker Desktop 不支援 host network，請改用 URL `http://host.docker.internal:8080/...`。

## Permission Model

`webtable`：

- `SELECT/INSERT/UPDATE/DELETE sqli_lab.users`: OK
- `CALL sp_get_user_by_id`, `sp_search_users`, `sp_create_user`, `sp_search_users_unsafe`: FAIL

`websp`：

- 直接 `SELECT/INSERT/UPDATE/DELETE sqli_lab.users`: FAIL
- `CALL sp_get_user_by_id`, `sp_search_users`, `sp_create_user`, `sp_search_users_unsafe`: OK
- CALL 未授權 SP: FAIL

所有 Stored Procedures 都使用：

```sql
SQL SECURITY DEFINER
DEFINER='sp_owner'@'%'
```

重點：Stored Procedure 不等於自動安全。安全 SP 不拼接 SQL；`sp_search_users_unsafe` 在 SP 內使用 `CONCAT + PREPARE + EXECUTE`，所以仍可 SQL Injection。

## Teaching Notes

- `password_plain` 是刻意保留的教學欄位，只用於展示資料外洩風險，正式系統不可明文存密碼。
- Web Lab 不使用 root。
- 不使用 root 當 SP DEFINER。
- 最小權限只能降低爆炸半徑，不能取代 Prepared Statement。
- Prepared Statement 是防 SQL Injection 的主要手段。
