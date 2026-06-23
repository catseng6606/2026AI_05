CREATE USER IF NOT EXISTS 'sp_owner'@'%' IDENTIFIED BY 'sp_owner_pass';
CREATE USER IF NOT EXISTS 'webtable'@'%' IDENTIFIED BY 'webtable_pass';
CREATE USER IF NOT EXISTS 'websp'@'%' IDENTIFIED BY 'websp_pass';

REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'sp_owner'@'%';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'webtable'@'%';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'websp'@'%';

GRANT SELECT, INSERT, UPDATE, DELETE ON sqli_lab.users TO 'sp_owner'@'%';
GRANT SELECT, INSERT, UPDATE, DELETE ON sqli_lab.users TO 'webtable'@'%';

GRANT EXECUTE ON PROCEDURE sqli_lab.sp_get_user_by_id TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_search_users TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_create_user TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_search_users_unsafe TO 'websp'@'%';

FLUSH PRIVILEGES;
