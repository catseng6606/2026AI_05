# SQL Injection Lab

## 目的
PHP + MySQL Docker 教學環境，示範 SQL Injection 攻擊與防護（PDO Prepared Statement、Stored Procedure、最小權限、sqlmap 驗證）。

## 架構
```
PHP 8.2 Apache
  ↓ PDO
MySQL 8.4
  ↑
Adminer
```

## 啟動
```bash
docker compose up -d --build
```

## 停止
```bash
docker compose down
```

## 重建 DB
```bash
docker compose down -v
docker compose up -d --build
```

## 網址
- PHP: http://localhost:8080
- Adminer: http://localhost:8081

## Adminer 登入
### root
- Server: mysql
- Username: root
- Password: rootpass
- Database: sqli_lab

### websp
- Server: mysql
- Username: websp
- Password: websp_pass
- Database: sqli_lab

## 教學流程
1. **A** unsafe.php - 示範 SQL Injection (GET)
2. **B** sqlmap unsafe - 用 sqlmap 掃描 unsafe.php
3. **C** safe.php - PDO Prepared Statement 防護
4. **D** sqlmap safe - sqlmap 掃不到
5. **E** callsp.php - Stored Procedure 封裝操作
6. **F** sp-unsafe.php - SP 內動態 SQL 仍危險
7. **G** Adminer 用 websp 登入：SELECT 失敗、CALL 成功
8. **H** scripts/test-websp-permission.sh

## sqlmap
```bash
./scripts/sqlmap-unsafe.sh
./scripts/sqlmap-safe.sh
./scripts/sqlmap-callsp.sh
./scripts/sqlmap-sp-unsafe.sh
./scripts/sqlmap-post-unsafe.sh
```

## 權限模型
- **root**: 初始化 DB
- **sp_owner**: SP DEFINER，有 users CRUD
- **websp**: PHP 使用，只能 EXECUTE 指定 SP，不能直接 CRUD table

## DEFINER / SQL SECURITY DEFINER
- SP 執行時用 DEFINER（sp_owner）權限
- websp 只要 EXECUTE 權限即可呼叫 SP
- 不要用 root 當 DEFINER
- sp_owner 只給必要權限

## 安全提醒
- 僅限本機 Docker Lab
- 禁止掃描未授權網站
- 正式環境不要公開 Adminer
- 正式環境需防火牆/VPN/Log/備份/最小權限

## 教學總結
- Prepared Statement 防止輸入變 SQL 指令
- Stored Procedure 封裝資料操作，但不是自動安全
- 最小權限降低漏洞發生後的傷害
- sqlmap 用於驗證防護，不是攻擊未授權目標
