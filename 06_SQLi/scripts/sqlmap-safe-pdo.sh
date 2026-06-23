#!/usr/bin/env sh
set -eu
docker compose run --rm sqlmap -u "http://localhost:8080/safe-pdo.php?id=1" --batch --level=2 --risk=1
