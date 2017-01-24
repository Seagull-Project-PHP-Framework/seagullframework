-- Last edited: Antonio J. Garcia 2007-04-21
-- Schema for /modules/messaging

BEGIN;

-- ==============================================================
--  Table: contact                                               
-- ==============================================================
create table contact 
(
   contact_id           INT4                 not null,
   usr_id               INT4                 not null,
   originator_id        INT4                 null,
   date_created         TIMESTAMP            not null,
   constraint PK_CONTACT primary key (contact_id)
);

-- ==============================================================
--  Sequence: contact_seq
-- ==============================================================

create sequence contact_seq;

-- ==============================================================
--  Index: usr_contact_fk                                        
-- ==============================================================
create index usr_contact_fk on contact 
(
   usr_id
);


-- ==============================================================
--  Table: instant_message                                       
-- ==============================================================
create table instant_message 
(
   instant_message_id   INT4                 not null,
   user_id_from         INT4                 not null,
   user_id_to           INT4                 not null,
   msg_time             TIMESTAMP            null,
   subject              VARCHAR(128)         null,
   body                 TEXT                 null,
   delete_status        INT2                 null,
   read_status          INT2                 null,
   constraint PK_INSTANT_MESSAGE primary key (instant_message_id)
);

-- ==============================================================
--  Sequence: instant_message_seq
-- ==============================================================

create sequence instant_message_seq;

-- ==============================================================
--  Index: usr_instant_from_fk                                   
-- ==============================================================
create index usr_instant_from_fk on instant_message 
(
   user_id_to
);

-- ==============================================================
--  Index: ust_instant_to_fk                                     
-- ==============================================================
create index ust_instant_to_fk on instant_message 
(
   user_id_from
);

COMMIT;
