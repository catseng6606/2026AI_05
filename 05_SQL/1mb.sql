-- ==========================================================
-- 1. 初始化資料表並寫入 100 萬筆「獨一無二作者」的資料
-- ==========================================================
DROP TABLE IF EXISTS News;

CREATE TABLE News (
    Id INTEGER PRIMARY KEY,
    Title TEXT NOT NULL,
    Author TEXT NOT NULL,
    Content TEXT NOT NULL,
    CreatedAt TEXT NOT NULL
);

-- 為了加快百萬筆寫入速度，開啟 SQLite 的快取優化
-- PRAGMA synchronous = OFF;
-- PRAGMA journal_mode = MEMORY;

WITH RECURSIVE cnt(x) AS (
    SELECT 1
    UNION ALL
    SELECT x + 1 FROM cnt WHERE x < 1000000
)
INSERT INTO News (Title, Author, Content, CreatedAt)
SELECT
    'Title ' || x,
    'Author_' || x, -- 讓每個 Author 都是獨一無二的（例如 Author_888888）
    'Content for news ' || x || '. This is a longer text to make table scanning heavier.',
    datetime('now', '-' || (x % 365) || ' days')
FROM cnt;

-- ==========================================================
-- 2. 【階段一：完全沒索引】
-- ==========================================================

-- 檢查執行計畫：你會看到 "SCAN News"
EXPLAIN QUERY PLAN
SELECT * FROM News WHERE Author = 'Author_888888';

-- 💡 請在此時執行這條 SQL 並記錄時間（這叫全表掃描，預期最慢）
-- SELECT * FROM News WHERE Author = 'Author_888888';


-- ==========================================================
-- 3. 【建立索引】
-- ==========================================================
CREATE INDEX idx_news_author ON News(Author);


-- ==========================================================
-- 4. 【階段二：有索引，但使用 SELECT *（需要回表/反查）】
-- ==========================================================

-- 檢查執行計畫：你會看到 "SEARCH News USING INDEX idx_news_author"
EXPLAIN QUERY PLAN
SELECT * FROM News WHERE Author = 'Author_888888';

-- 💡 請執行這條，並與階段一的時間比較（通常會變快，但因為要回表抓 Title, Content，所以還不是最快）
-- SELECT * FROM News WHERE Author = 'Author_888888';


-- ==========================================================
-- 5. 【階段三：有索引，且觸發「覆蓋索引」（完全不回表）】
-- ==========================================================

-- 檢查執行計畫：你會看到 "SEARCH News USING COVERING INDEX idx_news_author"
EXPLAIN QUERY PLAN
SELECT Author FROM News WHERE Author = 'Author_888888';

-- 💡 執行這條！時間通常會直接歸零（接近 0ms 或 1ms），這才是索引的完全體！
-- SELECT Author FROM News WHERE Author = 'Author_888888';