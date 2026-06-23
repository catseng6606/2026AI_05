#!/usr/bin/env sh
set -eu
docker compose run --rm sqlmap -u "http://localhost:8080/callsp.php?id=1&keyword=a" --batch --level=2 --risk=1
