-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Schema for /etc

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
   constraint PK_ITEM primary key (item_id)
);

-- ==============================================================
--  Index: item_item_type_fk                                     
-- ==============================================================
create  index item_item_type_fk on item 
(
   item_type_id
);

-- ==============================================================
--  Index: category_item_fk                                      
-- ==============================================================
create  index category_item_fk on item 
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
   constraint PK_ITEM_ADDITION primary key (item_addition_id)
);

-- ==============================================================
--  Index: item_item_addition_fk                                 
-- ==============================================================
create  index item_item_addition_fk on item_addition 
(
   item_id
);

-- ==============================================================
--  Index: item_type_mapping_item_addition                       
-- ==============================================================
create  index item_type_mapping_item_addition_fk on item_addition 
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
--  Index: item_type_item_type_mapping_fk                        
-- ==============================================================
create  index item_type_item_type_mapping_fk on item_type_mapping 
(
   item_type_id
);

-- ==============================================================
--  Table: log_table                                             
-- ==============================================================
create table log_table 
(
   id                   INT4                 not null,
   logtime              TIMESTAMP            not null,
   ident                CHAR(16)             not null,
   priority             INT4                 not null,
   message              VARCHAR(200)         null,
   constraint PK_LOG_TABLE primary key (id)
);

-- ==============================================================
--  Table: table_lock                                            
-- ==============================================================
create table table_lock 
(
   lockID               CHAR(32)             not null,
   lockTable            CHAR(32)             not null,
   lockStamp            INT4                 null,
   constraint PK_TABLE_LOCK primary key (lockID, lockTable)
);

-- ==============================================================
--  Table: session
-- ==============================================================
create table user_session 
(
   session_id                    VARCHAR(255)    not null,
   last_updated                  TIMESTAMP       null,
   data_value                    TEXT            null,
   usr_id                        INT4            not null,
   username                      VARCHAR(64)     null,
   expiry                        INT4            not null,   
   constraint PK_SESSION primary key (session_id)
);

create  index AK_user_session_keys on user_session (
    last_updated,
    usr_id,
    username
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

-- ==============================================================
--  Function: unix_timestamp
-- ==============================================================
-- for this to work, you have to activate the language plpgsql by calling
-- "createlang plpgsql <dbname>" from commandline.

CREATE or replace FUNCTION unix_timestamp (timestamp)
RETURNS integer AS ' DECLARE datum ALIAS FOR $1; BEGIN RETURN EXTRACT (EPOCH FROM datum); END; ' LANGUAGE plpgsql;
