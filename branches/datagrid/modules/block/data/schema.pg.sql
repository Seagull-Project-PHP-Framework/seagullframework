-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Schema for /modules/block

BEGIN;

-- ==============================================================
--  Table: block                                                 
-- ==============================================================
create table block 
(
   block_id             INT4                 not null,
   name                 VARCHAR(64)          null,
   title                VARCHAR(32)          null,
   title_class          VARCHAR(32)          null,
   body_class           VARCHAR(32)          null,
   blk_order            INT2                 null,
   is_onleft            INT2                 null,
   is_enabled           INT2                 null,
   content		text,
   constraint PK_BLOCK primary key (block_id)
);

-- ==============================================================
--  Table: block_assignment                                      
-- ==============================================================
create table block_assignment
(
   block_id             INT4                 not null,
   section_id           INT4                 not null,
   constraint PK_BLOCK_ASSIGNMENT primary key (block_id, section_id)
);

-- ==============================================================
--  Index: block_assignment_fk                                   
-- ==============================================================
create index block_assignment_fk on block_assignment 
(
   block_id
);

-- ==============================================================
--  Index: block_assignment_fk2                                  
-- ==============================================================
create index block_assignment_fk2 on block_assignment 
(
   section_id
);

-- ==============================================================
--  table block_roles
--  DK 
-- ==============================================================
create table block_roles (
    block_id integer not null,
    role_id integer not null
);

COMMIT;
