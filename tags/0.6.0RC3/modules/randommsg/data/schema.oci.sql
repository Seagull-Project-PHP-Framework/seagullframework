-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00

-- ==============================================================
--  Table: rndmsg_message
-- ==============================================================
create table rndmsg_message (
rndmsg_message_id             NUMBER(10)            not null,
msg                           CLOB            null,
constraint PK_RNDMSG_MESSAGE primary key (rndmsg_message_id)
);

create sequence rndmsg_message_seq;

