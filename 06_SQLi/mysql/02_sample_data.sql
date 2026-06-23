USE sqli_lab;

TRUNCATE TABLE users;
INSERT INTO users (username, email, password_plain, role) VALUES
('admin', 'admin@example.test', 'admin123', 'admin'),
('alice', 'alice@example.test', 'alice123', 'member'),
('bob', 'bob@example.test', 'bob123', 'member'),
('teacher', 'teacher@example.test', 'teach123', 'teacher'),
('student', 'student@example.test', 'student123', 'student');
