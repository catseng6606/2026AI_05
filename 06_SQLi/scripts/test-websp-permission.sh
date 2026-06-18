#!/usr/bin/env bash
set -e
echo "Expect FAIL: websp direct SELECT"
docker compose exec mysql mysql -uwebsp -pwebsp_pass sqli_lab -e "SELECT * FROM users;" || true
echo "Expect OK: websp CALL SP"
docker compose exec mysql mysql -uwebsp -pwebsp_pass sqli_lab -e "CALL sp_user_list();"
