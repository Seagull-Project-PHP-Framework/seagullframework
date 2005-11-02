-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00
--  Changes:        session -> sessions                  

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
constraint PK_ITEM primary key (item_id)
);

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
constraint PK_ITEM_ADDITION primary key (item_addition_id)
);

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
--  Index: item_type_item_type_mapping_fk                        
-- ==============================================================
create  index item_type_item_type_mapping_fk on item_type_mapping (
item_type_id
);

-- ==============================================================
--  Table: log_table                                             
-- ==============================================================
create table log_table (
id                   NUMBER(10)                 not null,
logtime              DATE            not null,
ident                CHAR(16)             not null,
priority             NUMBER(10)                 not null,
message              VARCHAR(200)         null,
constraint PK_LOG_TABLE primary key (id)
);

-- ==============================================================
--  Table: table_lock                                            
-- ==============================================================
create table table_lock (
lockID               CHAR(32)             not null,
lockTable            CHAR(32)             not null,
lockStamp            NUMBER(10)                 null,
constraint PK_TABLE_LOCK primary key (lockID, lockTable)
);

-- ==============================================================
--  Table: user_sessions
-- ==============================================================
CREATE TABLE user_session 
(
  session_id    varchar(255) NOT NULL,
  last_updated  DATE       null,
  data_value    CLOB            null,
  usr_id        NUMBER(10) 	NOT NULL,
  username      VARCHAR(64) DEFAULT NULL,
  expiry        NUMBER(10) 	NOT NULL,
  constraint PK_SESSION primary key (session_id)
);

-- ==============================================================
--  Index: user_session_last_updated                        
-- ==============================================================

create  index user_session_last_updated on user_session (
    last_updated
);

-- ==============================================================
--  Index: user_session_usr_id                        
-- ==============================================================

create  index user_session_usr_id on user_session (
    usr_id
);
    
-- ==============================================================
--  Index: user_session_username                        
-- ==============================================================

create  index user_session_username on user_session (
    username
);

-- ==============================================================
-- Table: category                                       
-- ==============================================================

CREATE TABLE category (
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
--  Index: root_id                                               
-- ==============================================================
create  index AK_category_root_id on category (
root_id
);

-- ==============================================================
--  Index: left_id                                               
-- ==============================================================
create  index AK_category_left_id on category (
left_id
);

-- ==============================================================
--  Index: ritgh_id                                              
-- ==============================================================
create  index AK_category_right_id on category (
right_id
);

-- ==============================================================
--  Index: order_id                                              
-- ==============================================================
create  index AK_category_order_id on category (
order_id
);

-- ==============================================================
--  Index: level_id                                              
-- ==============================================================
create  index AK_category_level_id on category (
level_id
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
--  Function: unix_timestamp
-- ==============================================================
CREATE FUNCTION unix_timestamp (datum IN VARCHAR2) RETURN NUMBER IS BEGIN RETURN ROUND((TO_DATE(datum) - TO_DATE('1970-01-01 00:00:00','YYYY-MM-DD HH24:MI:SS'))*86400,0); END;;


-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00

-- ==============================================================
--  Table: module                                                 
-- ==============================================================

create table module (
module_id         NUMBER(10) not null,
is_configurable   NUMBER(5) null,
name              VARCHAR(255) null,
title             VARCHAR(255) null,
description       CLOB         null,
admin_uri         VARCHAR(255) null,
icon              VARCHAR(255) null,
constraint PK_MODULE primary key (module_id)
);
