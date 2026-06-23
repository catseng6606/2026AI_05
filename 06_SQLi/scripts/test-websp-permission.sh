#!/usr/bin/env sh
set -eu
docker compose exec mysql mysql -uwebsp -pwebsp_pass sqli_lab -e "SELECT * FROM users LIMIT 1" && exit 1 || true
docker compose exec mysql mysql -uwebsp -pwebsp_pass sqli_lab -e "CALL sp_get_user_by_id(1)"
docker compose exec mysql mysql -uwebtable -pwebtable_pass sqli_lab -e "SELECT * FROM users LIMIT 1"
docker compose exec mysql mysql -uwebtable -pwebtable_pass sqli_lab -e "CALL sp_get_user_by_id(1)" && exit 1 || true
echo "permission checks matched expected failures/successes"
