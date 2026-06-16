-- 建立課程表 (course)
CREATE TABLE course (
    c_id VARCHAR(10) PRIMARY KEY, -- 課程編號
    c_name TEXT NOT NULL,         -- 課程名稱
    credits INTEGER               -- 學分
);

-- 建立學生表 (student)
CREATE TABLE student (
    s_id VARCHAR(10) PRIMARY KEY, -- 學號
    s_name TEXT NOT NULL,         -- 姓名
    c_id VARCHAR(10),             -- 課程編號 (外鍵)
    FOREIGN KEY (c_id) REFERENCES course(c_id)
);

-- 插入課程資料
INSERT INTO course (c_id, c_name, credits) VALUES ('C001', '會計一', 1);
INSERT INTO course (c_id, c_name, credits) VALUES ('C002', '俄羅斯史', 2);
INSERT INTO course (c_id, c_name, credits) VALUES ('C003', '三民主義', 3);
INSERT INTO course (c_id, c_name, credits) VALUES ('C004', '事業經濟', 4);
INSERT INTO course (c_id, c_name, credits) VALUES ('C005', '五權憲法', 5);

-- 插入學生資料
INSERT INTO student (s_id, s_name, c_id) VALUES ('S001', '趙一', 'C001');
INSERT INTO student (s_id, s_name, c_id) VALUES ('S002', '錢二', 'C002');
INSERT INTO student (s_id, s_name, c_id) VALUES ('S003', '張三', 'C005');
INSERT INTO student (s_id, s_name, c_id) VALUES ('S004', '李四', NULL);


