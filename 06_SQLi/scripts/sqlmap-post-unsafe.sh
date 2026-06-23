#!/usr/bin/env sh
set -eu
docker compose run --rm sqlmap -u "http://localhost:8080/post-unsafe.php" --data "username=alice" --batch --level=2 --risk=1
