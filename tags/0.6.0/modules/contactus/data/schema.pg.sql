-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Schema for /modules/contactus

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

-- ==============================================================
--  Sequence: contact_us_seq
-- ==============================================================

create sequence contact_us_seq;


COMMIT;
