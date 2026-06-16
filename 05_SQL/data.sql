-- 1. 建立大類資料表
CREATE TABLE MainCategory (
    main_id INTEGER PRIMARY KEY,
    main_name VARCHAR(50) NOT NULL
);

-- 2. 建立中類資料表
CREATE TABLE SubCategory (
    sub_id INTEGER PRIMARY KEY,
    main_id INTEGER,
    sub_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (main_id) REFERENCES MainCategory(main_id)
);

-- 3. 建立小類資料表
CREATE TABLE ProductItem (
    item_id INTEGER PRIMARY KEY AUTOINCREMENT,
    sub_id INTEGER,
    item_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (sub_id) REFERENCES SubCategory(sub_id)
);

-- 1. 大類 (範圍 1-3)

INSERT INTO MainCategory (main_id, main_name) VALUES 

(1, '3C'), 

(2, '家電'), 

(3, '美妝個清');

-- 2. 中類 (補完 5 筆)

INSERT INTO SubCategory (sub_id, main_id, sub_name) VALUES 

(101, 1, '手機/平板'),

(102, 1, '電腦/筆電'),

(201, 2, '生活家電'),

(202, 2, '廚房家電'),

(301, 3, '臉部保養');

-- 3. 小類 (每類 3 筆，依經驗補完)

INSERT INTO ProductItem (sub_id, item_name) VALUES 

-- 手機/平板

(101, 'iPhone 15'), (101, 'iPad Pro'), (101, 'Android 手機'),

-- 電腦/筆電

(102, 'MacBook Air'), (102, '電競筆電'), (102, '文書桌機'),

-- 生活家電

(201, '吸塵器'), (201, '空氣清淨機'), (201, '除濕機'),

-- 廚房家電

(202, '氣炸鍋'), (202, '電鍋'), (202, '微波爐'),

-- 臉部保養

(301, '化妝水'), (301, '精華液'), (301, '乳霜');