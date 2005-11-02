-- ==============================================================
--  DBMS name:      MaxDB
--  Created on:     2004-04-13 23:45:57
-- ==============================================================

-- Schema for /etc


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
--  Table: log_table
-- ==============================================================
CREATE TABLE log_table
(
   id                   INTEGER                 NOT  NULL,
   logtime              TIMESTAMP            NOT  NULL,
   ident                VARCHAR(16)             NOT  NULL,
   priority             INTEGER                 NOT  NULL,
   message              VARCHAR(200)         NULL
);

-- ==============================================================
--  Table: session
-- ==============================================================
CREATE TABLE user_session
(
   session_id                    VARCHAR(255)    NOT  NULL,
   last_updated                  TIMESTAMP       NULL,
   data_value                    LONG            NULL
);

-- ==============================================================
--  Table: table_lock
-- ==============================================================
CREATE TABLE table_lock
(
   lockID               VARCHAR(32)             NOT  NULL,
   lockTable            VARCHAR(32)             NOT  NULL,
   lockStamp            INTEGER                 NULL
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

-- ==============================================================
--  Function: unix_timestamp
-- You have to create the Function with SapDB SQL Studio
-- ==============================================================
-- DROP FUNCTION unix_timestamp;
-- CREATE FUNCTION unix_timestamp (sDatum VARCHAR) RETURNS INTEGER AS RETURN DATEDIFF(sDatum, '1970-01-01 00:00:00') * 86400;

COMMIT;


-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL
-- ==========================================================================

-- Schema for /modules/default


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--

-- ==============================================================
--  Table: module
-- ==============================================================

CREATE TABLE module
(
   module_id         INTEGER not null,
   is_configurable   INTEGER null,
   name              VARCHAR(255) null,
   title             VARCHAR(255) null,
   description       LONG         null,
   admin_uri         VARCHAR(255) null,
   icon              VARCHAR(255) null
);

-- ==============================================================
--  Alter primary Key
-- ==============================================================
ALTER TABLE module ADD PRIMARY KEY (module_id);


COMMIT;