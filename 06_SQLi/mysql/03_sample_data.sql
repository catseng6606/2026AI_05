CREATE DATABASE IF NOT EXISTS sqli_lab;
USE sqli_lab;

INSERT INTO users (username, email, role) VALUES
('alice', 'alice@example.test', 'student'),
('bob', 'bob@example.test', 'teacher'),
('charlie', 'charlie@example.test', 'student'),
('admin_demo', 'admin@example.test', 'admin'),
('test01', 'test01@example.test', 'student');
