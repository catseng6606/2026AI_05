#!/usr/bin/env bash
set -e
sqlmap -u "http://localhost:8080/callsp.php?action=get&id=1" -p id --batch
