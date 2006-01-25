-- ==========================================================================
-- Seagull PHP Framework: Default Data for MaxDB
-- ==========================================================================

-- Schema for /modules/navigation


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
-- ==============================================================
--  Table: section
-- ==============================================================
CREATE TABLE section
(
   section_id           INTEGER                 NOT  NULL,
   title                VARCHAR(32)          NULL,
   resource_uri         VARCHAR(128)         NULL,
   perms                VARCHAR(16)          NULL,
   parent_id            INTEGER                 NULL,
   root_id              INTEGER                 NULL,
   left_id              INTEGER                 NULL,
   right_id             INTEGER                 NULL,
   order_id             INTEGER                 NULL,
   level_id             INTEGER                 NULL,
   is_enabled           INTEGER                 NULL,
   is_static            INTEGER                 NULL
);

-- ==============================================================
-- Table: category
-- ==============================================================
CREATE TABLE category (
  category_id      INTEGER            NOT NULL default '0',
  label            VARCHAR(32)     default NULL,
  perms            VARCHAR(16)     default NULL,
  parent_id        INTEGER            default NULL,
  root_id          INTEGER            default NULL,
  left_id          INTEGER            default NULL,
  right_id         INTEGER            default NULL,
  order_id         INTEGER            default NULL,
  level_id         INTEGER            default NULL
);

-- ==============================================================
--  Primary Keys
-- ==============================================================
ALTER TABLE section ADD PRIMARY KEY (section_id);
ALTER TABLE category ADD PRIMARY KEY (category_id);

-- ==============================================================
--  Create Index
-- ==============================================================
CREATE INDEX ix_root_id ON section (root_id);
CREATE INDEX ix2_root_id ON category (root_id);
CREATE INDEX ix_left_id ON section (left_id);
CREATE INDEX ix2_left_id ON category (left_id);
CREATE INDEX ix_right_id ON section (right_id);
CREATE INDEX ix2_right_id ON category (right_id);
CREATE INDEX ix_order_id ON section (order_id);
CREATE INDEX ix2_order_id ON category (order_id);
CREATE INDEX ix_level_id ON section (level_id);
CREATE INDEX ix2_level_id ON category (level_id);
CREATE INDEX ix_section ON section
(
   section_id,
   root_id,
   left_id,
   right_id
);

CREATE INDEX ix_category ON category
(
    category_id,
    root_id,
    left_id,
    right_id
);



COMMIT;
