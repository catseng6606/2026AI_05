#!/bin/bash
# 本 Lab 僅限本機教學，禁止掃描未授權網站。
# 驗證 websp / webtable 權限模型
set -e

DB_HOST="${DB_HOST:-mysql}"
MYSQL_DATABASE="${MYSQL_DATABASE:-sqli_lab}"
WEBSP_PASSWORD="${WEBSP_PASSWORD:-websp_pass}"
WEBTABLE_PASSWORD="${WEBTABLE_PASSWORD:-webtable_pass}"

echo "=== websp 權限測試 ==="

echo -n "websp SELECT users → "
mysql -h "$DB_HOST" -u websp -p"$WEBSP_PASSWORD" "$MYSQL_DATABASE" -e "SELECT COUNT(*) FROM users;" 2>&1 || echo "FAIL (expected)"

echo -n "websp INSERT users → "
mysql -h "$DB_HOST" -u websp -p"$WEBSP_PASSWORD" "$MYSQL_DATABASE" -e "INSERT INTO users (username,email,password_plain,role) VALUES ('t','t@t.com','t','t');" 2>&1 || echo "FAIL (expected)"

echo -n "websp UPDATE users → "
mysql -h "$DB_HOST" -u websp -p"$WEBSP_PASSWORD" "$MYSQL_DATABASE" -e "UPDATE users SET username='x' WHERE id=999;" 2>&1 || echo "FAIL (expected)"

echo -n "websp DELETE users → "
mysql -h "$DB_HOST" -u websp -p"$WEBSP_PASSWORD" "$MYSQL_DATABASE" -e "DELETE FROM users WHERE id=999;" 2>&1 || echo "FAIL (expected)"

echo -n "websp CALL sp_get_user_by_id → "
mysql -h "$DB_HOST" -u websp -p"$WEBSP_PASSWORD" "$MYSQL_DATABASE" -e "CALL sp_get_user_by_id(1);" 2>&1 || echo "FAIL"

echo -n "websp CALL sp_search_users → "
mysql -h "$DB_HOST" -u websp -p"$WEBSP_PASSWORD" "$MYSQL_DATABASE" -e "CALL sp_search_users('a');" 2>&1 || echo "FAIL"

echo -n "websp CALL sp_create_user → "
mysql -h "$DB_HOST" -u websp -p"$WEBSP_PASSWORD" "$MYSQL_DATABASE" -e "CALL sp_create_user('t','t@t.com','t','t');" 2>&1 || echo "FAIL"

echo ""
echo "=== webtable 權限測試 ==="

echo -n "webtable SELECT users → "
mysql -h "$DB_HOST" -u webtable -p"$WEBTABLE_PASSWORD" "$MYSQL_DATABASE" -e "SELECT COUNT(*) FROM users;" 2>&1 || echo "FAIL"

echo -n "webtable CALL sp_get_user_by_id → "
mysql -h "$DB_HOST" -u webtable -p"$WEBTABLE_PASSWORD" "$MYSQL_DATABASE" -e "CALL sp_get_user_by_id(1);" 2>&1 || echo "FAIL (expected)"

echo ""
echo "=== Done ==="
