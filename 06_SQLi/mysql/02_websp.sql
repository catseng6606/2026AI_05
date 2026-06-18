CREATE DATABASE IF NOT EXISTS sqli_lab;
USE sqli_lab;

CREATE USER IF NOT EXISTS 'websp'@'%' IDENTIFIED BY 'websp_pass';

GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_list TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_get TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_create TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_update TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_delete TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_user_search_unsafe TO 'websp'@'%';

FLUSH PRIVILEGES;
