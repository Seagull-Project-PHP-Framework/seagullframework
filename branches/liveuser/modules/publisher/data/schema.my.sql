/*==============================================================*/
/* Table: document                                              */
/*==============================================================*/
create table if not exists document
(
   document_id                    int                            not null,
   category_id                    int,
   document_type_id               int                            not null,
   name                           varchar(128),
   file_size                      int,
   mime_type                      varchar(32),
   date_created                   datetime,
   added_by                       int,
   description                    text,
   num_times_downloaded           int,
   primary key (document_id)
);

/*==============================================================*/
/* Index: document_document_type_fk                             */
/*==============================================================*/
create index document_document_type_fk on document
(
   document_type_id
);

/*==============================================================*/
/* Index: category_document_fk                                  */
/*==============================================================*/
create index category_document_fk on document
(
   category_id
);

/*==============================================================*/
/* Table: document_type                                         */
/*==============================================================*/
create table if not exists document_type
(
   document_type_id               int                            not null,
   name                           varchar(32),
   primary key (document_type_id)
);

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
   hits                           int,
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
   trans_id                       int                           default '0',
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
