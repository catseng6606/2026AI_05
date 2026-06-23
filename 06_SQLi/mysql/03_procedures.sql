USE sqli_lab;

DROP PROCEDURE IF EXISTS sp_get_user_by_id;
DROP PROCEDURE IF EXISTS sp_search_users;
DROP PROCEDURE IF EXISTS sp_create_user;
DROP PROCEDURE IF EXISTS sp_search_users_unsafe;

CREATE DEFINER='sp_owner'@'%' PROCEDURE sp_get_user_by_id(IN p_id INT)
SQL SECURITY DEFINER
SELECT id, username, email, role, created_at FROM users WHERE id = p_id;

CREATE DEFINER='sp_owner'@'%' PROCEDURE sp_search_users(IN p_keyword VARCHAR(100))
SQL SECURITY DEFINER
SELECT id, username, email, role, created_at
FROM users
WHERE username LIKE CONCAT('%', p_keyword, '%')
   OR email LIKE CONCAT('%', p_keyword, '%');

CREATE DEFINER='sp_owner'@'%' PROCEDURE sp_create_user(
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_password_plain VARCHAR(100),
    IN p_role VARCHAR(30)
)
SQL SECURITY DEFINER
INSERT INTO users (username, email, password_plain, role)
VALUES (p_username, p_email, p_password_plain, p_role);

CREATE DEFINER='sp_owner'@'%' PROCEDURE sp_search_users_unsafe(IN p_keyword VARCHAR(100))
SQL SECURITY DEFINER
BEGIN
    SET @sql = CONCAT(
        'SELECT id, username, email, role, created_at FROM users ',
        'WHERE username LIKE ''%', p_keyword, '%'' OR email LIKE ''%', p_keyword, '%'''
    );
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END;
