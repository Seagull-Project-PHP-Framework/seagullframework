-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL
-- ==========================================================================

-- Schema for /modules/guestbook


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
-- ==============================================================
--  Table: guestbook
-- ==============================================================
create table guestbook
(
   guestbook_id    INTEGER             not null,
   date_created    TIMESTAMP            null,
   name            VARCHAR(255)         null,
   email           VARCHAR(255)         null,
   message         LONG                 null
);

ALTER TABLE guestbook ADD PRIMARY KEY (guestbook_id);

COMMIT;