-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00

-- ==============================================================
--  Table: faq                                                   
-- ==============================================================
create table faq (
faq_id               NUMBER(10)                 not null,
date_created         DATE            null,
last_updated         DATE            null,
question             VARCHAR(255)         null,
answer               CLOB                 null,
item_order           NUMBER(10)                 null,
constraint PK_FAQ primary key (faq_id)
);

create sequence faq_seq;

