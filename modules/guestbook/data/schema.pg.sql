-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Schema for /modules/guestbook

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

-- ==============================================================
--  Sequence: guestbook_seq
-- ==============================================================

create sequence guestbook_seq;


COMMIT;
