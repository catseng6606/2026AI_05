-- PRAGMA foreign_keys = ON; -- SQLite 預設是不啟用外鍵約束檢查的
-- FOREIGN KEY (本表欄位) REFERENCES 來源表(來源欄位) ON DELETE [行為]


CREATE TABLE Categories (
    CategoryID INTEGER PRIMARY KEY,
    Name TEXT
);

CREATE TABLE Products (
    ProductID INTEGER PRIMARY KEY,
    ProductName TEXT,
    CatID INTEGER,
    -- 設定為 RESTRICT：若分類下有產品，則不准刪除該分類
    FOREIGN KEY (CatID) REFERENCES Categories(CategoryID) ON DELETE RESTRICT
);


CREATE TABLE Products (
    ProductID INTEGER PRIMARY KEY,
    ProductName TEXT,
    CatID INTEGER,
    -- 設定為 CASCADE：分類刪除，產品自動跟著消失
    FOREIGN KEY (CatID) REFERENCES Categories(CategoryID) ON DELETE CASCADE
);

CREATE TABLE Products (
    ProductID INTEGER PRIMARY KEY,
    ProductName TEXT,
    CatID INTEGER,
    -- 設定為 SET NULL：分類刪除後，產品的 CatID 變為空值
    FOREIGN KEY (CatID) REFERENCES Categories(CategoryID) ON DELETE SET NULL
);

CREATE TABLE Products (
    ProductID INTEGER PRIMARY KEY,
    ProductName TEXT,
    CatID INTEGER DEFAULT 1, -- 預設值設為 1
    -- 設定為 SET DEFAULT：分類刪除後，CatID 回歸預設值 1
    FOREIGN KEY (CatID) REFERENCES Categories(CategoryID) ON DELETE SET DEFAULT
);

-- 務必先啟動外鍵支援
PRAGMA foreign_keys = ON;

-- 建立分類表
CREATE TABLE Categories (
    CategoryID INTEGER PRIMARY KEY,
    CategoryName TEXT NOT NULL
);

-- 插入測試分類
INSERT INTO Categories (CategoryID, CategoryName) VALUES (1, '電子產品');
INSERT INTO Categories (CategoryID, CategoryName) VALUES (2, '書籍');


CREATE TABLE Products_Cascade (
    ProductID INTEGER PRIMARY KEY,
    ProductName TEXT,
    CatID INTEGER,
    FOREIGN KEY (CatID) REFERENCES Categories(CategoryID) ON DELETE CASCADE
);

-- 插入資料：將「手機」歸類在「電子產品(1)」
INSERT INTO Products_Cascade (ProductName, CatID) VALUES ('手機', 1);




-- (重置分類 2 以供測試)
-- 建立產品表
CREATE TABLE Products_SetNull (
    ProductID INTEGER PRIMARY KEY,
    ProductName TEXT,
    CatID INTEGER,
    FOREIGN KEY (CatID) REFERENCES Categories(CategoryID) ON DELETE SET NULL
);

-- 插入資料：將「SQL 指南」歸類在「書籍(2)」
INSERT INTO Products_SetNull (ProductName, CatID) VALUES ('SQL 指南', 2);




-- (先插入一個新的分類 3)
INSERT INTO Categories (CategoryID, CategoryName) VALUES (3, '食品');

CREATE TABLE Products_Restrict (
    ProductID INTEGER PRIMARY KEY,
    ProductName TEXT,
    CatID INTEGER,
    FOREIGN KEY (CatID) REFERENCES Categories(CategoryID) ON DELETE RESTRICT
);

INSERT INTO Products_Restrict (ProductName, CatID) VALUES ('巧克力', 3);




-- 執行測試：刪除分類 1
DELETE FROM Categories WHERE CategoryID = 1;

-- 驗證：你會發現 Products_Cascade 裡的「手機」也被自動刪除了
SELECT * FROM Products_Cascade;

-- 執行測試：刪除分類 2
DELETE FROM Categories WHERE CategoryID = 2;

-- 驗證：產品「SQL 指南」依然存在，但其 CatID 變成了 NULL
SELECT * FROM Products_SetNull;

-- 執行測試：嘗試刪除分類 3
-- 執行這行會報錯：Runtime error: FOREIGN KEY constraint failed
DELETE FROM Categories WHERE CategoryID = 3;