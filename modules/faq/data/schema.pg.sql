-- Last edited: Pierpaolo Toniolo 29-03-2006
-- Schema for /faq

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

-- ==============================================================
--  Sequence: faq_seq
-- ==============================================================

create sequence faq_seq;

COMMIT;
