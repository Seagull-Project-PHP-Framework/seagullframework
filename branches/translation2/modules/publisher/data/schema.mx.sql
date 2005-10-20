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

COMMIT;