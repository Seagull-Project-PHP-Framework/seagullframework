-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL 
-- ==========================================================================

-- Schema for /modules/guestbook


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

-- ==============================================================
--  Table: guestbook
-- ==============================================================
create table guestbook 
(
   guestbook_id    INT4                 not null,
   date_created    TIMESTAMP            null,
   name            VARCHAR(255)         null,
   email           VARCHAR(255)         null,
   message         TEXT                 null,
   constraint PK_GUESTBOOK primary key (guestbook_id)
);

COMMIT;