-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL 
-- ==========================================================================

-- Schema for /modules/navigation


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
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

-- ==============================================================
-- Table: category
-- ==============================================================
create table category (
  category_id      INT4            NOT NULL default '0',
  label            VARCHAR(32)     default NULL,
  perms            VARCHAR(16)     default NULL,
  parent_id        INT4            default NULL,
  root_id          INT4            default NULL,
  left_id          INT4            default NULL,
  right_id         INT4            default NULL,
  order_id         INT4            default NULL,
  level_id         INT4            default NULL,
  constraint PK_category PRIMARY KEY (category_id)
);

-- ==============================================================
--  Index: root_id                                               
-- ==============================================================
create index AK_category_key_root_id on category
(
    root_id
);


-- ==============================================================
--  Index: order_id
-- ==============================================================
create index AK_category_key_order_id on category
(
    order_id
);

-- ==============================================================
--  Index: left_id
-- ==============================================================
create index AK_category_key_left_id on category
(
    left_id
);

-- ==============================================================
--  Index: right_id
-- ==============================================================
create index AK_category_key_right_id on category
(
    right_id
);

-- ==============================================================
--  Index: root_l_r
-- ==============================================================
create index AK_category_id_root_l_r on category
(
    category_id,
    root_id,
    left_id,
    right_id
);

-- ==============================================================
--  Index: level_id
-- ==============================================================
create index AK_category_key_level_id on category
(
    level_id
);

COMMIT;
