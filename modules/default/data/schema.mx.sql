-- ==============================================================
--  DBMS name:      MaxDB
--  Created on:     2004-04-13 23:45:57
-- ==============================================================

-- Schema for default

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
--  Function: unix_timestamp
-- You have to create the Function with SapDB SQL Studio
-- ==============================================================
-- DROP FUNCTION unix_timestamp;
-- CREATE FUNCTION unix_timestamp (sDatum VARCHAR) RETURNS INTEGER AS RETURN DATEDIFF(sDatum, '1970-01-01 00:00:00') * 86400;


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