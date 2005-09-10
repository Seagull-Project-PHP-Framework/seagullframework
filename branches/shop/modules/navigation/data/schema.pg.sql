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
   perms                VARCHAR(16)          null,
   parent_id            INT4                 null,
   root_id              INT4                 null,
   left_id              INT4                 null,
   right_id             INT4                 null,
   order_id             INT4                 null,
   level_id             INT4                 null,
   is_enabled           INT2                 null,
   is_static            INT2                 null,
   access_key           CHAR(1)              null,
   rel                  VARCHAR(16)          null,   
   constraint PK_SECTION primary key (section_id)
);

-- ==============================================================
--  Index: root_id                                               
-- ==============================================================
create index AK_section_key_root_id on section 
(
   root_id
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
--  Index: order_id                                              
-- ==============================================================
create index AK_section_key_order_id on section 
(
   order_id
);

-- ==============================================================
--  Index: level_id                                              
-- ==============================================================
create index AK_section_key_level_id on section 
(
   level_id
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

COMMIT;

