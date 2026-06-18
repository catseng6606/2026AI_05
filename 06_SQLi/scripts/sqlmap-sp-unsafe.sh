#!/usr/bin/env bash
set -e
sqlmap -u "http://localhost:8080/sp-unsafe.php?keyword=a" -p keyword --batch
