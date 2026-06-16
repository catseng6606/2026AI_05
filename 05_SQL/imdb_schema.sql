-- 人員表 (People)
create table main.people
(
    person_id VARCHAR -- 人員 ID
        primary key,
    name      VARCHAR, -- 姓名
    born      INTEGER, -- 出生年份
    died      INTEGER  -- 逝世年份
);

-- 索引：人員姓名
create index main.ix_people_name
    on main.people (name);

-- 索引：人員 ID
create index main.ix_people_person_id
    on main.people (person_id);

-- SQLite 系統表 (System Table)
create table main.sqlite_master
(
    type     TEXT,
    name     TEXT,
    tbl_name TEXT,
    rootpage INT,
    sql      TEXT
);

-- 別名表 (Alternative Titles / AKAs)
create table main.akas
(
    title_id          VARCHAR, -- 影片 ID
    title             VARCHAR, -- 片名
    region            VARCHAR, -- 地區
    language          VARCHAR, -- 語言
    types             VARCHAR, -- 類型
    attributes        VARCHAR, -- 屬性
    is_original_title INTEGER  -- 是否為原始片名
);

-- 索引：片名
create index main.ix_akas_title
    on main.akas (title);

-- 索引：影片 ID
create index main.ix_akas_title_id
    on main.akas (title_id);

-- 劇組人員表 (Crew)
create table main.crew
(
    title_id   VARCHAR, -- 影片 ID
    person_id  VARCHAR, -- 人員 ID
    category   VARCHAR, -- 職務類別 (如 director, writer)
    job        VARCHAR, -- 具體工作
    characters VARCHAR  -- 飾演角色
);

-- 索引：職務類別
create index main.ix_crew_category
    on main.crew (category);

-- 索引：人員 ID
create index main.ix_crew_person_id
    on main.crew (person_id);

-- 索引：影片 ID
create index main.ix_crew_title_id
    on main.crew (title_id);

-- 影集集數表 (Episodes)
create table main.episodes
(
    episode_title_id VARCHAR, -- 單集影片 ID
    show_title_id    VARCHAR, -- 影集 ID
    season_number    INTEGER, -- 季數
    episode_number   INTEGER  -- 集數
);

-- 索引：單集影片 ID
create index main.ix_episodes_episode_title_id
    on main.episodes (episode_title_id);

-- 索引：影集 ID
create index main.ix_episodes_show_title_id
    on main.episodes (show_title_id);

-- 評分表 (Ratings)
create table main.ratings
(
    title_id VARCHAR -- 影片 ID
        primary key,
    rating   INTEGER, -- 評分
    votes    INTEGER  -- 投票數
);

-- 影片資訊表 (Titles)
create table main.titles
(
    title_id        VARCHAR -- 影片 ID
        primary key,
    type            VARCHAR, -- 影片類型 (如 movie, short, tvSeries)
    primary_title   VARCHAR, -- 主要片名
    original_title  VARCHAR, -- 原始片名
    is_adult        INTEGER, -- 是否為成人內容 (0/1)
    premiered       INTEGER, -- 首映年份
    ended           INTEGER, -- 結束年份 (若為影集)
    runtime_minutes INTEGER, -- 片長 (分鐘)
    genres          VARCHAR  -- 類型 (如 Action, Comedy)
);

-- 索引：原始片名
create index main.ix_titles_original_title
    on main.titles (original_title);

-- 索引：主要片名
create index main.ix_titles_primary_title
    on main.titles (primary_title);

-- 索引：影片類型
create index main.ix_titles_type
    on main.titles (type);

