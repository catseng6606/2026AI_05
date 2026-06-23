USE sqli_lab;

INSERT IGNORE INTO users (id, username, email, password_plain, role) VALUES
(1, 'admin', 'admin@sqli-lab.local', 'admin123', 'administrator'),
(2, 'alice', 'alice@sqli-lab.local', 'alice_pass', 'editor'),
(3, 'bob', 'bob@sqli-lab.local', 'b0b_P@ss', 'viewer'),
(4, 'teacher', 'teacher@sqli-lab.local', 'teach_2024', 'instructor'),
(5, 'student', 'student@sqli-lab.local', 'stud_pass', 'learner');
