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