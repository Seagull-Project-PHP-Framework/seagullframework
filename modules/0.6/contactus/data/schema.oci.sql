-- ==============================================================
--  DBMS name:      Oracle 9.x                             
--  Created on:     2004-12-15 10:32:00
--  Changes:        contact_us.comment -> contact_us.comments     

-- ==============================================================
--  Table: contact_us                                            
-- ==============================================================
create table contact_us
(
	contact_us_id        NUMBER(10)           not null,
	first_name           VARCHAR(64)          null,
	last_name            VARCHAR(32)          null,
	email                VARCHAR(128)         null,
	enquiry_type         VARCHAR(32)          null,
	user_comment         CLOB                 null,
	constraint PK_CONTACT_US primary key (contact_us_id)
);

create sequence contact_us_seq;

