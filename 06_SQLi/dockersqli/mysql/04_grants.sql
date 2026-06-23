USE sqli_lab;

-- sp_owner: full CRUD on users + routine creation
DROP USER IF EXISTS 'sp_owner'@'%';
CREATE USER 'sp_owner'@'%' IDENTIFIED BY 'sp_owner_pass';
GRANT SELECT, INSERT, UPDATE, DELETE ON sqli_lab.users TO 'sp_owner'@'%';
GRANT CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON sqli_lab.* TO 'sp_owner'@'%';

-- webtable: direct CRUD on users
DROP USER IF EXISTS 'webtable'@'%';
CREATE USER 'webtable'@'%' IDENTIFIED BY 'webtable_pass';
GRANT SELECT, INSERT, UPDATE, DELETE ON sqli_lab.users TO 'webtable'@'%';

-- websp: only EXECUTE on specific procedures
DROP USER IF EXISTS 'websp'@'%';
CREATE USER 'websp'@'%' IDENTIFIED BY 'websp_pass';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_get_user_by_id TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_search_users TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_create_user TO 'websp'@'%';
GRANT EXECUTE ON PROCEDURE sqli_lab.sp_search_users_unsafe TO 'websp'@'%';

FLUSH PRIVILEGES;
