-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL
-- ==========================================================================

-- Schema for /modules/randommsg


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
-- ==============================================================
--  Table: rndmsg_message
-- ==============================================================
create table rndmsg_message
(
   rndmsg_message_id             INTEGER        not null,
   msg                           LONG            null
);

ALTER TABLE rndmsg_message ADD PRIMARY KEY (rndmsg_message_id);

COMMIT;