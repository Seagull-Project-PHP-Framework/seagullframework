-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Schema for /modules/randommsg

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
