DROP TABLE IF EXISTS News;

CREATE TABLE News (
    Id INTEGER PRIMARY KEY,
    Title TEXT NOT NULL,
    Author TEXT NOT NULL,
    Content TEXT NOT NULL,
    CreatedAt TEXT NOT NULL
);

WITH RECURSIVE cnt(x) AS (
    SELECT 1
    UNION ALL
    SELECT x + 1 FROM cnt WHERE x < 1000000
)
INSERT INTO News (Title, Author, Content, CreatedAt)
SELECT
    'Title ' || x,
    'Author' || (x % 1000),
    'Content for news ' || x,
    datetime('now', '-' || (x % 365) || ' days')
FROM cnt;



EXPLAIN QUERY PLAN
SELECT *
FROM News
WHERE Author = 'Author888';

CREATE INDEX idx_news_author
ON News(Author);