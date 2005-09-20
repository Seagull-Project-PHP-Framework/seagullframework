/*==============================================================*/
/* DBMS name:      MySQL 4.0                                    */
/* Created on:     2004-04-05 01:05:58                          */
/*==============================================================*/

/*==============================================================*/
/* Table: item                                                  */
/*==============================================================*/
create table if not exists item
(
   item_id                        int                            not null,
   category_id                    int,
   item_type_id                   int                            not null,
   created_by_id                  int,
   updated_by_id                  int,
   date_created                   datetime,
   last_updated                   datetime,
   start_date                     datetime,
   expiry_date                    datetime,
   status                         smallint,
   primary key (item_id)
);

/*==============================================================*/
/* Index: item_item_type_fk                                     */
/*==============================================================*/
create index item_item_type_fk on item
(
   item_type_id
);

/*==============================================================*/
/* Index: category_item_fk                                      */
/*==============================================================*/
create index category_item_fk on item
(
   category_id
);

/*==============================================================*/
/* Table: item_addition                                         */
/*==============================================================*/
create table if not exists item_addition
(
   item_addition_id               int                            not null,
   item_id                        int                            not null,
   item_type_mapping_id           int                            not null,
   addition                       text,
   primary key (item_addition_id)
);

/*==============================================================*/
/* Index: item_item_addition_fk                                 */
/*==============================================================*/
create index item_item_addition_fk on item_addition
(
   item_id
);

/*==============================================================*/
/* Index: item_type_mapping_item_addition_fk                    */
/*==============================================================*/
create index item_type_mapping_item_addition_fk on item_addition
(
   item_type_mapping_id
);

/*==============================================================*/
/* Table: item_type                                             */
/*==============================================================*/
create table if not exists item_type
(
   item_type_id                   int                            not null,
   item_type_name                 varchar(64),
   primary key (item_type_id)
);

/*==============================================================*/
/* Table: item_type_mapping                                     */
/*==============================================================*/
create table if not exists item_type_mapping
(
   item_type_mapping_id           int                            not null,
   item_type_id                   int                            not null,
   field_name                     varchar(64),
   field_type                     smallint,
   primary key (item_type_mapping_id)
);

/*==============================================================*/
/* Index: item_type_item_type_mapping_fk                        */
/*==============================================================*/
create index item_type_item_type_mapping_fk on item_type_mapping
(
   item_type_id
);

/*==============================================================*/
/* Table: log_table                                             */
/*==============================================================*/
create table if not exists log_table
(
   id                             int                            not null,
   logtime                        timestamp                      not null,
   ident                          char(16)                       not null,
   priority                       int                            not null,
   message                        varchar(200),
   primary key (id)
);

/*==============================================================*/
/* Table: table_lock                                            */
/*==============================================================*/
create table if not exists table_lock
(
   lockID                         char(32)                       not null,
   lockTable                      char(32)                       not null,
   lockStamp                      int,
   primary key (lockID, lockTable)
);

/*==============================================================*/
/* Table: user_session                                          */
/*==============================================================*/
CREATE TABLE user_session (
  session_id varchar(255) NOT NULL,
  last_updated datetime,
  data_value text,
  usr_id int(11) NOT NULL,
  username varchar(64) default NULL,
  expiry int(11) NOT NULL,
  PRIMARY KEY  (session_id),
  KEY last_updated (last_updated),
  KEY usr_id (usr_id),
  KEY username (username)
);

/*==============================================================*/
/* Table: category                                              */
/*==============================================================*/
create table if not exists category (
  category_id int(11) NOT NULL default '0',
  label varchar(32) default NULL,
  perms varchar(32) default NULL,
  parent_id int(11) default NULL,
  root_id int(11) default NULL,
  left_id int(11) default NULL,
  right_id int(11) default NULL,
  order_id int(11) default NULL,
  level_id int(11) default NULL,
  PRIMARY KEY  (category_id),
  KEY AK_key_root_id (root_id),
  KEY AK_key_order_id (order_id),
  KEY AK_key_left_id (left_id),
  KEY AK_key_right_id (right_id),
  KEY AK_id_root_l_r (category_id,root_id,left_id,right_id),
  KEY AK_key_level_id (level_id)
);

/*==============================================================*/
/* Index: parent_fk                                             */
/*==============================================================*/
create index parent_fk on category
(
   parent_id
);