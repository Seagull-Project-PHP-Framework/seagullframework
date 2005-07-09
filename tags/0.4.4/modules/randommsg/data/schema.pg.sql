-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL 
-- ==========================================================================

-- Schema for /modules/randommsg


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

-- ==============================================================
--  Table: rndmsg_message
-- ==============================================================
create table rndmsg_message 
(
   rndmsg_message_id             INT4            not null,
   msg                           TEXT            null,
   constraint PK_RNDMSG_MESSAGE primary key (rndmsg_message_id)
);

COMMIT;