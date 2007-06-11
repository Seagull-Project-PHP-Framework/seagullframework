-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Schema for /modules/navigation

BEGIN;

-- ==============================================================
--  Table: section                                               
-- ==============================================================
create table section 
(
   section_id           INT4                 not null,
   title                VARCHAR(32)          null,
   resource_uri         VARCHAR(128)         null,
   perms                VARCHAR(32)          null,
   trans_id             INT4,
   parent_id            INT4,
   root_id              INT4,
   left_id              INT4,
   right_id             INT4,
   order_id             INT4,
   level_id             INT4,
   is_enabled           INT2,
   is_static            INT2,
   access_key           CHAR(1)              null,
   rel                  VARCHAR(16)          null,   
   constraint PK_SECTION primary key (section_id)
);

-- ==============================================================
--  Sequence: section_seq
-- ==============================================================

create sequence section_seq;

-- ==============================================================
--  Index: root_id                                               
-- ==============================================================
create index AK_section_key_root_id on section 
(
   root_id
);

-- ==============================================================
--  Index: order_id                                              
-- ==============================================================
create index AK_section_key_order_id on section 
(
   order_id
);

-- ==============================================================
--  Index: left_id                                               
-- ==============================================================
create index AK_section_key_left_id on section 
(
   left_id
);

-- ==============================================================
--  Index: right_id                                              
-- ==============================================================
create index AK_section_key_rigth_id on section 
(
   right_id
);

-- ==============================================================
--  Index: id_root_l_r                                           
-- ==============================================================
create index AK_section_id_root_l_r on section 
(
   section_id,
   root_id,
   left_id,
   right_id
);

-- ==============================================================
--  Index: level_id                                              
-- ==============================================================
create index AK_section_key_level_id on section 
(
   level_id
);

-- ==============================================================
-- Table: uri_alias
-- ==============================================================
create table uri_alias (
   uri_alias_id       INT4     NOT NULL   default '0',
   uri_alias          VARCHAR(255)        NULL,
   section_id         INT4                NULL,
   title              VARCHAR(255)        NULL,
   keywords           TEXT,
   description        TEXT,
   PRIMARY KEY  (uri_alias_id)
) ;

-- ==============================================================
--  Sequence: uri_alias_seq
-- ==============================================================

create sequence uri_alias_seq;

-- ==============================================================
-- Index: uri_alias
-- ==============================================================
create unique index UK_uri_alias ON uri_alias (
    uri_alias
);


COMMIT;

