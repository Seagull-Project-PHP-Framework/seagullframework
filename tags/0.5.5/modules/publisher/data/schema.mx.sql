-- ==========================================================================
-- Seagull PHP Framework: Default Data for MaxDB
-- ==========================================================================

-- Schema for /modules/publisher


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
-- ==============================================================
--  Table: document
-- ==============================================================
CREATE TABLE document
(
   document_id          INTEGER                 NOT  NULL,
   category_id          INTEGER                 NULL,
   document_type_id     INTEGER                 NOT  NULL,
   name                 VARCHAR(128)         NULL,
   file_size            INTEGER                 NULL,
   mime_type            VARCHAR(32)          NULL,
   date_created         TIMESTAMP            NULL,
   added_by             INTEGER                 NULL,
   description          LONG                 NULL,
   num_times_downloaded INTEGER                 NULL
);

-- ==============================================================
--  Table: document_type
-- ==============================================================
CREATE TABLE document_type
(
   document_type_id     INTEGER                 NOT  NULL,
   name                 VARCHAR(32)          NULL
);

-- ==============================================================
--  Primary Keys
-- ==============================================================
ALTER TABLE document ADD PRIMARY KEY (document_id);
ALTER TABLE document_type ADD PRIMARY KEY (document_type_id);

-- ==============================================================
--  Index: document_document_type_fk
-- ==============================================================
CREATE INDEX ix_document_type_id ON document
(
   document_type_id
);

-- ==============================================================
--  Index: category_document_fk
-- ==============================================================
CREATE INDEX ix_category_id ON document
(
   category_id
);


-- ==============================================================
--  Table: item
-- ==============================================================
CREATE TABLE item
(
   item_id              INTEGER                 NOT  NULL,
   category_id          INTEGER                 NULL,
   item_type_id         INTEGER                 NOT  NULL,
   created_by_id        INTEGER                 NULL,
   updated_by_id        INTEGER                 NULL,
   date_created         TIMESTAMP            	 NULL,
   last_updated         TIMESTAMP            	 NULL,
   start_date           TIMESTAMP            	 NULL,
   expiry_date          TIMESTAMP            	 NULL,
   status               INTEGER                 NULL
);

-- ==============================================================
--  Table: item_addition
-- ==============================================================
CREATE TABLE item_addition
(
   item_addition_id     INTEGER                 NOT  NULL,
   item_id              INTEGER                 NOT  NULL,
   item_type_mapping_id INTEGER                 NOT  NULL,
   addition             LONG                 NULL
);


-- ==============================================================
--  Table: item_type
-- ==============================================================
CREATE TABLE item_type
(
   item_type_id         INTEGER                 NOT  NULL,
   item_type_name       VARCHAR(64)          NULL
);

-- ==============================================================
--  Table: item_type_mapping
-- ==============================================================
CREATE TABLE item_type_mapping
(
   item_type_mapping_id INTEGER                 NOT  NULL,
   item_type_id         INTEGER                 NOT  NULL,
   field_name           VARCHAR(64)          NULL,
   field_type           INTEGER                 NULL
);


-- ==============================================================
--  Alter primary Key
-- ==============================================================
ALTER TABLE item ADD PRIMARY KEY (item_id);
ALTER TABLE item_addition ADD PRIMARY KEY (item_addition_id);
ALTER TABLE item_type ADD PRIMARY KEY (item_type_id);
ALTER TABLE item_type_mapping ADD PRIMARY KEY (item_type_mapping_id);
ALTER TABLE log_table ADD PRIMARY KEY (id);
ALTER TABLE user_session ADD PRIMARY KEY (session_id);
ALTER TABLE table_lock ADD PRIMARY KEY (lockID, lockTable);

-- ==============================================================
--  Index: item_item_type_fk
-- ==============================================================
CREATE INDEX ix_item_type_id ON item
(
   item_type_id
);

-- ==============================================================
--  Index: category_item_fk
-- ==============================================================
CREATE INDEX ix_category_id ON item
(
   category_id
);

-- ==============================================================
--  Index: item_item_addition_fk
-- ==============================================================
CREATE INDEX ix_item_id ON item_addition
(
   item_id
);

-- ==============================================================
--  Index: item_type_mapping_item_addition
-- ==============================================================
CREATE INDEX ix_item_type_mapping_id ON item_addition
(
   item_type_mapping_id
);
-- ==============================================================
--  Index: item_type_item_type_mapping_fk
-- ==============================================================
CREATE INDEX ix_item_type_id ON item_type_mapping
(
   item_type_id
);


COMMIT;