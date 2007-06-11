-- Last edited: Pierpaolo Toniolo 29-03-2006
-- Schema for /randommsg

BEGIN;

-- ==============================================================
--  Table: rndmsg_message
-- ==============================================================
create table rndmsg_message (
   rndmsg_message_id             INT4            not null,
   msg                           TEXT            null,
   constraint PK_RNDMSG_MESSAGE primary key (rndmsg_message_id)
);

-- ==============================================================
--  Sequence: rndmsg_message_seq
-- ==============================================================

create sequence rndmsg_message_seq;


COMMIT;
