-- ==========================================================================
-- Seagull PHP Framework: Default Data for MaxDB
-- ==========================================================================

-- Schema for /modules/messaging


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
-- ==============================================================
--  Table: contact
-- ==============================================================
CREATE TABLE contact
(
   contact_id           INTEGER                 NOT  NULL,
   usr_id               INTEGER                 NOT  NULL,
   originator_id        INTEGER                 NULL,
   date_created         TIMESTAMP            NOT  NULL
);

-- ==============================================================
--  Table: instant_message
-- ==============================================================
CREATE TABLE instant_message
(
   instant_message_id   INTEGER                 NOT  NULL,
   user_id_from         INTEGER                 NOT  NULL,
   user_id_to           INTEGER                 NOT  NULL,
   msg_time             TIMESTAMP            NULL,
   subject              VARCHAR(128)         NULL,
   body                 LONG                 NULL,
   delete_status        INTEGER                 NULL,
   read_status          INTEGER                 NULL
);

-- ==============================================================
--  Alter primary Key
-- ==============================================================
ALTER TABLE contact ADD PRIMARY KEY (contact_id);
ALTER TABLE instant_message ADD PRIMARY KEY (instant_message_id);

-- ==============================================================
--  Index: ix4_usr_id
-- ==============================================================
CREATE INDEX ix4_usr_id ON contact (usr_id);

-- ==============================================================
--  Index: ix_user_id_to
-- ==============================================================
CREATE INDEX ix_user_id_to ON instant_message
(
   user_id_to
);

-- ==============================================================
--  Index: ix_user_id_from
-- ==============================================================
CREATE INDEX ix_user_id_from ON instant_message
(
   user_id_from
);




COMMIT;