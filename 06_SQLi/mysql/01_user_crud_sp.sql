CREATE DATABASE IF NOT EXISTS sqli_lab;
USE sqli_lab;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'student',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE USER IF NOT EXISTS 'sp_owner'@'%' IDENTIFIED BY 'sp_owner_pass';
GRANT SELECT, INSERT, UPDATE, DELETE ON sqli_lab.users TO 'sp_owner'@'%';

DELIMITER //

CREATE DEFINER = 'sp_owner'@'%'
PROCEDURE sp_user_list()
SQL SECURITY DEFINER
BEGIN
  SELECT id, username, email, role, created_at FROM users ORDER BY id;
END //

CREATE DEFINER = 'sp_owner'@'%'
PROCEDURE sp_user_get(IN p_id INT)
SQL SECURITY DEFINER
BEGIN
  SELECT id, username, email, role, created_at FROM users WHERE id = p_id;
END //

CREATE DEFINER = 'sp_owner'@'%'
PROCEDURE sp_user_create(
  IN p_username VARCHAR(50),
  IN p_email VARCHAR(100),
  IN p_role VARCHAR(20)
)
SQL SECURITY DEFINER
BEGIN
  INSERT INTO users (username, email, role) VALUES (p_username, p_email, p_role);
  SELECT LAST_INSERT_ID() AS id;
END //

CREATE DEFINER = 'sp_owner'@'%'
PROCEDURE sp_user_update(
  IN p_id INT,
  IN p_username VARCHAR(50),
  IN p_email VARCHAR(100),
  IN p_role VARCHAR(20)
)
SQL SECURITY DEFINER
BEGIN
  UPDATE users SET username = p_username, email = p_email, role = p_role WHERE id = p_id;
END //

CREATE DEFINER = 'sp_owner'@'%'
PROCEDURE sp_user_delete(IN p_id INT)
SQL SECURITY DEFINER
BEGIN
  DELETE FROM users WHERE id = p_id;
END //

-- Intentionally unsafe SP for teaching demo
-- 此 SP 故意不安全，僅供教學。內部使用 CONCAT 動態 SQL，可被 SQL Injection。
CREATE DEFINER = 'sp_owner'@'%'
PROCEDURE sp_user_search_unsafe(IN p_keyword VARCHAR(100))
SQL SECURITY DEFINER
BEGIN
  SET @sql = CONCAT("SELECT id, username, email, role FROM users WHERE username LIKE '%", p_keyword, "%'");
  PREPARE stmt FROM @sql;
  EXECUTE stmt;
  DEALLOCATE PREPARE stmt;
END //

DELIMITER ;
