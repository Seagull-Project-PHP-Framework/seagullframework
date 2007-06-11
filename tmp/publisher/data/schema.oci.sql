-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00
--  Changes:        document.size -> document.docsize    

-- ==============================================================
--  Table: document                                              
-- ==============================================================
create table document (
document_id          NUMBER(10)                 not null,
category_id          NUMBER(10)                 null,
document_type_id     NUMBER(10)                 not null,
name                 VARCHAR(128)         null,
file_size            NUMBER(10)                 null,
mime_type            VARCHAR(32)          null,
date_created         DATE            null,
added_by             NUMBER(10)                 null,
description          CLOB                 null,
num_times_downloaded NUMBER(10)                 null,
constraint PK_DOCUMENT primary key (document_id)
);

-- ==============================================================
--  Sequence: document_seq
-- ==============================================================

create sequence document_seq;

-- ==============================================================
--  Index: document_document_type_fk                             
-- ==============================================================
create  index document_document_type_fk on document (
document_type_id
);

-- ==============================================================
--  Index: category_document_fk                                  
-- ==============================================================
create  index category_document_fk on document (
category_id
);

-- ==============================================================
--  Table: document_type                                         
-- ==============================================================
create table document_type (
document_type_id     NUMBER(10)                 not null,
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
create table item (
item_id              NUMBER(10)                 not null,
category_id          NUMBER(10)                 null,
item_type_id         NUMBER(10)                 not null,
created_by_id        NUMBER(10)                 null,
updated_by_id        NUMBER(10)                 null,
date_created         DATE            null,
last_updated         DATE            null,
start_date           DATE            null,
expiry_date          DATE            null,
status               NUMBER(5)                 null,
hits                 NUMBER(10),
constraint PK_ITEM primary key (item_id)
);

-- ==============================================================
--  Sequence: item_seq
-- ==============================================================

create sequence item_seq;

-- ==============================================================
--  Index: item_item_type_fk                                     
-- ==============================================================
create  index item_item_type_fk on item (
item_type_id
);

-- ==============================================================
--  Index: category_item_fk                                      
-- ==============================================================
create  index category_item_fk on item (
category_id
);

-- ==============================================================
--  Table: item_addition                                         
-- ==============================================================
create table item_addition (
item_addition_id     NUMBER(10)                 not null,
item_id              NUMBER(10)                 not null,
item_type_mapping_id NUMBER(10)                 not null,
addition             CLOB                 null,
trans_id             NUMBER(10)                 default '0',
constraint PK_ITEM_ADDITION primary key (item_addition_id)
);

-- ==============================================================
--  Sequence: item_addition_seq
-- ==============================================================

create sequence item_addition_seq;

-- ==============================================================
--  Index: item_item_addition_fk                                 
-- ==============================================================
create  index item_item_addition_fk on item_addition (
item_id
);

-- ==============================================================
--  Index: item_type_mapping_item_addition                       
-- ==============================================================
create  index item_type_mapping_item_add on item_addition (
item_type_mapping_id
);

-- ==============================================================
--  Table: item_type                                             
-- ==============================================================
create table item_type (
item_type_id         NUMBER(10)                 not null,
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
create table item_type_mapping (
item_type_mapping_id NUMBER(10)                 not null,
item_type_id         NUMBER(10)                 not null,
field_name           VARCHAR(64)          null,
field_type           NUMBER(5)                 null,
constraint PK_ITEM_TYPE_MAPPING primary key (item_type_mapping_id)
);

-- ==============================================================
--  Sequence: item_type_mapping_seq
-- ==============================================================

create sequence item_type_mapping_seq;

-- ==============================================================
--  Index: item_type_item_type_mapping_fk                        
-- ==============================================================
create  index item_type_item_type_mapping_fk on item_type_mapping (
item_type_id
);

-- ==============================================================
-- Table: category                                       
-- ==============================================================

create table category (
  category_id NUMBER(10) 	NOT NULL,
  label VARCHAR(32) 		DEFAULT NULL,
  perms VARCHAR(32) 		DEFAULT NULL,
  parent_id NUMBER(10) 		DEFAULT NULL,
  root_id NUMBER(10) 		DEFAULT NULL,
  left_id NUMBER(10) 		DEFAULT NULL,
  right_id NUMBER(10) 		DEFAULT NULL,
  order_id NUMBER(10) 		DEFAULT NULL,
  level_id NUMBER(10) 		DEFAULT NULL,
  CONSTRAINT PK_CATEGORY PRIMARY KEY (category_id)
);

-- ==============================================================
--  Sequence: category_seq
-- ==============================================================

create sequence category_seq;

-- ==============================================================
--  Index: root_id                                               
-- ==============================================================
create  index AK_category_root_id on category (
root_id
);

-- ==============================================================
--  Index: order_id                                              
-- ==============================================================
create  index AK_category_order_id on category (
order_id
);

-- ==============================================================
--  Index: left_id                                               
-- ==============================================================
create  index AK_category_left_id on category (
left_id
);

-- ==============================================================
--  Index: right_id                                              
-- ==============================================================
create  index AK_category_right_id on category (
right_id
);

-- ==============================================================
--  Index: id_root_l_r                                           
-- ==============================================================
create  index AK_category_id_root_l_r on category (
category_id,
root_id,
left_id,
right_id
);

-- ==============================================================
--  Index: level_id                                              
-- ==============================================================
create  index AK_category_level_id on category (
level_id
);

-- ==============================================================
--  Index: parent_id
-- ==============================================================
create index AK_category_key_parent_fk on category
(
    parent_id
);


