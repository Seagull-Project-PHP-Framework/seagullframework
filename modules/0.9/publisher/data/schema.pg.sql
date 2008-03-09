-- Last edited: Antonio J. Garcia 2007-04-21
-- Schema for /publisher

BEGIN;

-- ==============================================================
--  Table: document                                              
-- ==============================================================
create table document 
(
   document_id          INT4                 not null,
   category_id          INT4                 null,
   document_type_id     INT4                 not null,
   name                 VARCHAR(128)         null,
   file_size            INT4                 null,
   mime_type            VARCHAR(32)          null,
   date_created         TIMESTAMP            null,
   added_by             INT4                 null,
   description          TEXT                 null,
   num_times_downloaded INT4                 null,
   constraint PK_DOCUMENT primary key (document_id)
);

-- ==============================================================
--  Sequence: document_seq
-- ==============================================================

create sequence document_seq;

-- ==============================================================
--  Index: document_document_type_fk                             
-- ==============================================================
create index document_document_type_fk on document 
(
   document_type_id
);

-- ==============================================================
--  Index: category_document_fk                                  
-- ==============================================================
create index category_document_fk on document 
(
   category_id
);

-- ==============================================================
--  Table: document_type                                         
-- ==============================================================
create table document_type 
(
   document_type_id     INT4                 not null,
   name                 VARCHAR(32)          null,
   constraint PK_DOCUMENT_TYPE primary key (document_type_id)
);

-- ==============================================================
--  Sequence: document_type_seq
-- ==============================================================

create sequence document_type_seq;


-- ==============================================================
--  Table: item                                                  
-- ==============================================================
create table item 
(
   item_id              INT4                 not null,
   category_id          INT4                 null,
   item_type_id         INT4                 not null,
   created_by_id        INT4                 null,
   updated_by_id        INT4                 null,
   date_created         TIMESTAMP            null,
   last_updated         TIMESTAMP            null,
   start_date           TIMESTAMP            null,
   expiry_date          TIMESTAMP            null,
   status               INT2                 null,
   hits                 INT4,
   constraint PK_ITEM primary key (item_id)
);

-- ==============================================================
--  Sequence: item_seq
-- ==============================================================

create sequence item_seq;

-- ==============================================================
--  Index: item_item_type_fk                                     
-- ==============================================================
create index item_item_type_fk on item 
(
   item_type_id
);

-- ==============================================================
--  Index: category_item_fk                                      
-- ==============================================================
create index category_item_fk on item 
(
   category_id
);

-- ==============================================================
--  Table: item_addition                                         
-- ==============================================================
create table item_addition 
(
   item_addition_id     INT4                 not null,
   item_id              INT4                 not null,
   item_type_mapping_id INT4                 not null,
   addition             TEXT                 null,
   trans_id             INT4                 default '0',
   constraint PK_ITEM_ADDITION primary key (item_addition_id)
);

-- ==============================================================
--  Sequence: item_addition_seq
-- ==============================================================

create sequence item_addition_seq;

-- ==============================================================
--  Index: item_item_addition_fk                                 
-- ==============================================================
create index item_item_addition_fk on item_addition 
(
   item_id
);

-- ==============================================================
--  Index: item_type_mapping_item_addition                       
-- ==============================================================
create index item_type_mapping_item_addition_fk on item_addition 
(
   item_type_mapping_id
);

-- ==============================================================
--  Table: item_type                                             
-- ==============================================================
create table item_type 
(
   item_type_id         INT4                 not null,
   item_type_name       VARCHAR(64)          null,
   constraint PK_ITEM_TYPE primary key (item_type_id)
);

-- ==============================================================
--  Sequence: item_type_seq
-- ==============================================================

create sequence item_type_seq;

-- ==============================================================
--  Table: item_type_mapping                                     
-- ==============================================================
create table item_type_mapping 
(
   item_type_mapping_id INT4                 not null,
   item_type_id         INT4                 not null,
   field_name           VARCHAR(64)          null,
   field_type           INT2                 null,
   constraint PK_ITEM_TYPE_MAPPING primary key (item_type_mapping_id)
);

-- ==============================================================
--  Sequence: item_type_mapping_seq
-- ==============================================================

create sequence item_type_mapping_seq;

-- ==============================================================
--  Index: item_type_item_type_mapping_fk                        
-- ==============================================================
create index item_type_item_type_mapping_fk on item_type_mapping 
(
   item_type_id
);

-- ==============================================================
-- Table: category
-- ==============================================================
create table category (
  category_id      INT4            NOT NULL default '0',
  label            VARCHAR(32)     default NULL,
  perms            VARCHAR(32)     default NULL,
  parent_id        INT4            default NULL,
  root_id          INT4            default NULL,
  left_id          INT4            default NULL,
  right_id         INT4            default NULL,
  order_id         INT4            default NULL,
  level_id         INT4            default NULL,
  constraint PK_category PRIMARY KEY (category_id)
);

-- ==============================================================
--  Sequence: category_seq
-- ==============================================================

create sequence category_seq;

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

-- ==============================================================
--  Index: parent_id
-- ==============================================================
create index AK_category_key_parent_fk on category
(
    parent_id
);


COMMIT;
