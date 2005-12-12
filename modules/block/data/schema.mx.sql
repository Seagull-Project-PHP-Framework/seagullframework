-- ==========================================================================
-- Seagull PHP Framework: Default Data for MaxDB
-- ==========================================================================

-- Schema for /modules/block


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--


-- ==============================================================
--  Table: block
-- ==============================================================
CREATE TABLE block
(
   block_id             INTEGER                 not null,
   name                 VARCHAR(64)          null,
   title                VARCHAR(32)          null,
   title_class          VARCHAR(32)          null,
   body_class           VARCHAR(32)          null,
   blk_order            INTEGER                 null,
   position             VARCHAR(16)             null,
   is_enabled           INTEGER                 null
);

-- ==============================================================
--  Table: block_assignment
-- ==============================================================
CREATE TABLE block_assignment
(
   block_id             INTEGER                 not null,
   section_id           INTEGER                 not null
);

-- ==============================================================
--  Alter primary Key
-- ==============================================================
ALTER TABLE block ADD PRIMARY KEY (block_id);
ALTER TABLE block_assignment ADD PRIMARY KEY (block_id, section_id);

-- ==============================================================
--  Index: ix_block_id
-- ==============================================================
CREATE INDEX ix_block_id ON block_assignment
(
   block_id
);

-- ==============================================================
--  Index: ix_section_id
-- ==============================================================
CREATE INDEX ix_section_id ON block_assignment
(
   section_id
);

COMMIT;