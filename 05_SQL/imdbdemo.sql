-- 2. CRUD 的題目

-- Create (新增)
-- 新增一部測試電影
INSERT INTO main.titles (title_id, type, primary_title, original_title, is_adult, premiered, genres)
VALUES ('tt9999999', 'movie', 'Demo Movie', 'Demo Movie Original', 0, 2025, 'Action');

-- Read (查詢)
-- 查詢剛剛新增的電影
SELECT * FROM main.titles WHERE title_id = 'tt9999999';

-- Update (修改)
-- 先新增對應的評分資料 (因為 ratings 表有 foreign key 概念，雖 SQLite 預設不強制，但邏輯上需要)
INSERT INTO main.ratings (title_id, rating, votes) VALUES ('tt9999999', 8.5, 100);
-- 修改評分
UPDATE main.ratings SET rating = 9.0 WHERE title_id = 'tt9999999';
-- 驗證修改
SELECT * FROM main.ratings WHERE title_id = 'tt9999999';

-- Delete (刪除)
-- 刪除測試資料
DELETE FROM main.titles WHERE title_id = 'tt9999999';
DELETE FROM main.ratings WHERE title_id = 'tt9999999';


-- 3. LIMIT (sqlite)
-- 取出前 10 筆電影資料
SELECT * FROM main.titles LIMIT 10;

-- 分頁效果：跳過前 10 筆，取接續的 5 筆
SELECT * FROM main.titles LIMIT 5 OFFSET 10;


-- 4. 排序 （搭配索引看差異）
-- 有索引 (primary_title 有建立索引 ix_titles_primary_title)
-- 執行計畫通常會顯示使用索引 (SCAN TABLE ... USING INDEX)
EXPLAIN QUERY PLAN
SELECT * FROM main.titles ORDER BY primary_title LIMIT 10;

-- 無索引 (runtime_minutes 未建立索引)
-- 執行計畫通常會顯示全表掃描並排序 (SCAN TABLE ...; USE TEMP B-TREE FOR ORDER BY)
EXPLAIN QUERY PLAN
SELECT * FROM main.titles ORDER BY runtime_minutes DESC LIMIT 10;


-- 5. 聚合函數包含 count , sum ,avg ,min,max
SELECT 
    COUNT(*) AS total_titles,       -- 總資料筆數
    AVG(runtime_minutes) AS avg_runtime, -- 平均片長
    MIN(premiered) AS first_year,   -- 最早年份
    MAX(premiered) AS last_year     -- 最晚年份
FROM main.titles;

-- 計算總投票數 (從 ratings 表)
SELECT SUM(votes) AS total_votes FROM main.ratings;


-- 6. group by
-- 依影片類型 (type) 分組，計算每種類型的數量
SELECT type, COUNT(*) AS count
FROM main.titles
GROUP BY type;


-- 7. having
-- 依影片類型分組，只顯示數量超過 1000 的類型
SELECT type, COUNT(*) AS count
FROM main.titles
GROUP BY type
HAVING COUNT(*) > 1000;


-- 8. join (inner join) (left join) (right join)

-- Inner Join: 只顯示兩邊都有資料的 (即有評分的電影)
SELECT t.primary_title, r.rating, r.votes
FROM main.titles t
INNER JOIN main.ratings r ON t.title_id = r.title_id
LIMIT 20;

-- Left Join: 顯示所有電影，若無評分則 rating/votes 為 NULL
SELECT t.primary_title, r.rating
FROM main.titles t
LEFT JOIN main.ratings r ON t.title_id = r.title_id
LIMIT 20;

-- Right Join: SQLite 不直接支援 RIGHT JOIN
-- 可以透過交換表格順序並使用 LEFT JOIN 來達成相同效果
-- 或者使用 UNION ALL 模擬 (較複雜)
-- 下列語法在 SQLite 會報錯：
-- SELECT t.primary_title, r.rating
-- FROM main.titles t
-- RIGHT JOIN main.ratings r ON t.title_id = r.title_id;


-- 9. union , union all

-- Union: 合併結果並 "去除重複"
-- 例如：找出所有導演 (director) 和編劇 (writer) 的 person_id
SELECT person_id FROM main.crew WHERE category = 'director'
UNION
SELECT person_id FROM main.crew WHERE category = 'writer';

-- Union All: 合併結果但 "不去除重複" (若某人既是導演也是編劇，會出現兩次)
SELECT person_id FROM main.crew WHERE category = 'director'
UNION ALL
SELECT person_id FROM main.crew WHERE category = 'writer';


-- 10. sub query
-- 找出評分高於 "所有電影平均評分" 的電影
-- 1. 先計算平均評分: SELECT AVG(rating) FROM main.ratings
-- 2. 再篩選大於該值的電影
SELECT t.primary_title, r.rating
FROM main.titles t
JOIN main.ratings r ON t.title_id = r.title_id
WHERE r.rating > (SELECT AVG(rating) FROM main.ratings)
ORDER BY r.rating DESC
LIMIT 20;


-- 11. 字串處理 (String Operations)
-- 搜尋片名包含 "Star Wars" 的電影 (不分大小寫)
SELECT * FROM main.titles 
WHERE primary_title LIKE '%Star Wars%' 
LIMIT 10;

-- 字串串接 (Concatenation)
SELECT primary_title || ' (' || premiered || ')' AS title_with_year
FROM main.titles
LIMIT 10;

-- 大小寫轉換
SELECT UPPER(primary_title) AS upper_title
FROM main.titles
LIMIT 5;


-- 12. 條件邏輯 (CASE WHEN)
-- 將電影長度分類為 Short, Medium, Long
SELECT primary_title, runtime_minutes,
    CASE
        WHEN runtime_minutes < 90 THEN 'Short'
        WHEN runtime_minutes BETWEEN 90 AND 150 THEN 'Medium'
        ELSE 'Long'
    END AS duration_category
FROM main.titles
WHERE runtime_minutes IS NOT NULL
LIMIT 20;


-- 13. 空值處理 (NULL Handling)
-- 找出沒有上映年份的電影
SELECT * FROM main.titles WHERE premiered IS NULL LIMIT 10;

-- 使用 COALESCE (或 IFNULL) 提供預設值
SELECT primary_title, COALESCE(premiered, 'Unknown') AS premiered_year
FROM main.titles
WHERE premiered IS NULL
LIMIT 10;


-- 14. Common Table Expressions (CTE)
-- 使用 CTE 找出評分最高的 5 部 Action 電影
WITH ActionMovies AS (
    SELECT title_id, primary_title
    FROM main.titles
    WHERE genres LIKE '%Action%'
)
SELECT am.primary_title, r.rating
FROM ActionMovies am
JOIN main.ratings r ON am.title_id = r.title_id
ORDER BY r.rating DESC
LIMIT 5;


-- 15. 視窗函數 (Window Functions)
-- 依類型分組，並列出每種類型中評分最高的 3 部電影 (Rank)
SELECT * FROM (
    SELECT t.type, t.primary_title, r.rating,
           RANK() OVER (PARTITION BY t.type ORDER BY r.rating DESC) as rank
    FROM main.titles t
    JOIN main.ratings r ON t.title_id = r.title_id
) 
WHERE rank <= 3;


-- 16. 交易控制 (Transactions)
-- 示範：同時新增電影與評分，確保原子性 (Atomicity)
BEGIN TRANSACTION;

INSERT INTO main.titles (title_id, type, primary_title, original_title, is_adult, premiered, genres)
VALUES ('tt8888888', 'movie', 'Transaction Demo', 'Transaction Demo', 0, 2025, 'Drama');

INSERT INTO main.ratings (title_id, rating, votes)
VALUES ('tt8888888', 7.5, 50);

COMMIT; -- 確認變更 (若有錯誤可使用 ROLLBACK)

-- 驗證
SELECT * FROM main.titles WHERE title_id = 'tt8888888';
SELECT * FROM main.ratings WHERE title_id = 'tt8888888';

-- 清理
DELETE FROM main.titles WHERE title_id = 'tt8888888';
DELETE FROM main.ratings WHERE title_id = 'tt8888888';
