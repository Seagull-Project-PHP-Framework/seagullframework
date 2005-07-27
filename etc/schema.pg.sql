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
   constraint PK_SESSION primary key (session_id)
);

