-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL 
-- ==========================================================================

-- Schema for /modules/default


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;


-- ==============================================================
--  Table: module                                                 
-- ==============================================================

create table module 
(
   module_id         INT4 not null,
   is_configurable   INT2 null,
   name              VARCHAR(255) null,
   title             VARCHAR(255) null,
   description       TEXT         null,
   admin_uri         VARCHAR(255) null,
   icon              VARCHAR(255) null,
   constraint PK_MODULE primary key (module_id)
);

COMMIT;