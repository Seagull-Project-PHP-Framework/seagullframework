-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL 
-- ==========================================================================

-- Schema for /modules/contactus


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

-- ==============================================================
--  Table: contact_us                                            
-- ==============================================================
create table contact_us 
(
   contact_us_id        INT4                 not null,
   first_name           VARCHAR(64)          null,
   last_name            VARCHAR(32)          null,
   email                VARCHAR(128)         null,
   enquiry_type         VARCHAR(32)          null,
   user_comment         TEXT                 null,
   constraint PK_CONTACT_US primary key (contact_us_id)
);


COMMIT;