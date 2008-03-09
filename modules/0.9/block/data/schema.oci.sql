-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:27:00

-- ==============================================================
--  Table: block                                                 
-- ==============================================================
create table block
(
	block_id             NUMBER(10)                 not null,
	name                 VARCHAR(64)          		null,
	title                VARCHAR(32)          		null,
	title_class          VARCHAR(32)          		null,
	body_class           VARCHAR(32)          		null,
	blk_order            NUMBER(5)                 	null,
	position             VARCHAR(16)                 	null,
	is_enabled           NUMBER(5)                 	null,
        is_cached            NUMBER(5)                          null,
	params               CLOB,
	constraint PK_BLOCK primary key (block_id)
);

-- ==============================================================
--  sequence block_seq
-- ==============================================================
create sequence block_seq;

-- ==============================================================
--  Table: block_assignment                                      
-- ==============================================================
create table block_assignment
(
	block_id             NUMBER(10)                 not null,
	section_id           NUMBER(10)                 not null,
	constraint PK_BLOCK_ASSIGNMENT primary key (block_id, section_id)
);

-- ==============================================================
--  sequence block_assignment_seq
-- ==============================================================
create sequence block_assignment_seq;

-- ==============================================================
--  Index: block_assignment_fk                                   
-- ==============================================================
create  index block_assignment_fk on block_assignment
(
	block_id
);

-- ==============================================================
--  Index: block_assignment_fk2                                  
-- ==============================================================
create  index block_assignment_fk2 on block_assignment
(
	section_id
);

-- ==============================================================
--  Table: block_role
-- ==============================================================
create table block_role (
	block_id 	NUMBER(10)	not null,
	role_id		NUMBER(10)	not null
);

-- ==============================================================
--  sequence block_role_seq
-- ==============================================================
create sequence block_role_seq;


