USE sqli_lab;

DELIMITER //

DROP PROCEDURE IF EXISTS sp_get_user_by_id//
CREATE PROCEDURE sp_get_user_by_id(IN p_id INT)
    SQL SECURITY DEFINER
    DEFINER = 'sp_owner'@'%'
BEGIN
    SELECT id, username, email, password_plain, role, created_at
    FROM users
    WHERE id = p_id;
END//

DROP PROCEDURE IF EXISTS sp_search_users//
CREATE PROCEDURE sp_search_users(IN p_keyword VARCHAR(100))
    SQL SECURITY DEFINER
    DEFINER = 'sp_owner'@'%'
BEGIN
    SELECT id, username, email, password_plain, role, created_at
    FROM users
    WHERE username LIKE CONCAT('%', p_keyword, '%')
       OR email LIKE CONCAT('%', p_keyword, '%');
END//

DROP PROCEDURE IF EXISTS sp_create_user//
CREATE PROCEDURE sp_create_user(
    IN p_username VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_password_plain VARCHAR(100),
    IN p_role VARCHAR(30)
)
    SQL SECURITY DEFINER
    DEFINER = 'sp_owner'@'%'
    MODIFIES SQL DATA
BEGIN
    INSERT INTO users (username, email, password_plain, role)
    VALUES (p_username, p_email, p_password_plain, p_role);
    SELECT LAST_INSERT_ID() AS new_id;
END//

DROP PROCEDURE IF EXISTS sp_search_users_unsafe//
CREATE PROCEDURE sp_search_users_unsafe(IN p_keyword VARCHAR(100))
    SQL SECURITY DEFINER
    DEFINER = 'sp_owner'@'%'
BEGIN
    SET @sql = CONCAT(
        'SELECT id, username, email, password_plain, role, created_at FROM users WHERE username LIKE ''%',
        p_keyword,
        '%'' OR email LIKE ''%',
        p_keyword,
        '%'''
    );
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END//

DELIMITER ;
