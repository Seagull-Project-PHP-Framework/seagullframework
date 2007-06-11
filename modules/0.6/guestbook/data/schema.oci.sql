-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00

-- ==============================================================
--  Table: guestbook
-- ==============================================================
create table guestbook (
guestbook_id    NUMBER(10)                 not null,
date_created    DATE            null,
name            VARCHAR(255)         null,
email           VARCHAR(255)         null,
message         CLOB                 null,
constraint PK_GUESTBOOK primary key (guestbook_id)
);

create sequence guestbook_seq;

