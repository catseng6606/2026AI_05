#!/usr/bin/env bash
set -e
sqlmap -u "http://localhost:8080/safe.php?id=1" -p id --batch
