-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL
-- ==========================================================================

-- Schema for /modules/faq


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
-- ==============================================================
--  Table: faq
-- ==============================================================
CREATE TABLE faq
(
   faq_id               INTEGER                 not null,
   date_created         TIMESTAMP            null,
   last_updated         TIMESTAMP            null,
   question             VARCHAR(255)         null,
   answer               LONG                 null,
   item_order           INTEGER                 null
);

-- ==============================================================
--  Alter primary Key
-- ==============================================================
ALTER TABLE faq ADD PRIMARY KEY (faq_id);

COMMIT;