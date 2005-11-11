-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL 
-- ==========================================================================

-- Schema for /modules/faq


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

-- ==============================================================
--  Table: faq                                                   
-- ==============================================================
create table faq 
(
   faq_id               INT4                 not null,
   date_created         TIMESTAMP            null,
   last_updated         TIMESTAMP            null,
   question             VARCHAR(255)         null,
   answer               TEXT                 null,
   item_order           INT4                 null,
   constraint PK_FAQ primary key (faq_id)
);

COMMIT;