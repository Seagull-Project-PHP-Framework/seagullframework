-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00

-- ==============================================================
--  Table: contact                                               
-- ==============================================================
create table contact (
contact_id           NUMBER(10)                 not null,
usr_id               NUMBER(10)                 not null,
originator_id        NUMBER(10)                 null,
date_created         DATE            not null,
constraint PK_CONTACT primary key (contact_id)
);

-- ==============================================================
--  Sequence: contact_seq
-- ==============================================================

create sequence contact_seq;

-- ==============================================================
--  Index: usr_contact_fk                                        
-- ==============================================================
create  index usr_contact_fk on contact (
usr_id
);

-- ==============================================================
--  Table: instant_message                                       
-- ==============================================================
create table instant_message (
instant_message_id   NUMBER(10)                 not null,
user_id_from         NUMBER(10)                 not null,
user_id_to           NUMBER(10)                 not null,
msg_time             DATE            null,
subject              VARCHAR(128)         null,
body                 CLOB                 null,
delete_status        NUMBER(5)                 null,
read_status          NUMBER(5)                 null,
constraint PK_INSTANT_MESSAGE primary key (instant_message_id)
);

-- ==============================================================
--  Sequence: instant_message_seq
-- ==============================================================

create sequence instant_message_seq;

-- ==============================================================
--  Index: usr_instant_from_fk                                   
-- ==============================================================
create  index usr_instant_from_fk on instant_message (
user_id_to
);

-- ==============================================================
--  Index: ust_instant_to_fk                                     
-- ==============================================================
create  index ust_instant_to_fk on instant_message (
user_id_from
);



