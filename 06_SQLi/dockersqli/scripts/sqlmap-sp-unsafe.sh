#!/bin/bash
# 本 Lab 僅限本機教學，禁止掃描未授權網站。
set -e
echo "[*] sqlmap - SP Unsafe Dynamic SQL (sp-unsafe.php)"
sqlmap -u "http://localhost:8080/sp-unsafe.php?keyword=a" --batch --level=2 --risk=1
echo "[*] Done"
