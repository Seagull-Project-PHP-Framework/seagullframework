/*==============================================================*/
/* Table: section                                               */
/*==============================================================*/
create table if not exists section
(
   section_id                     int                            not null,
   title                          varchar(32),
   resource_uri                   varchar(128),
   perms                          varchar(16),
   parent_id                      int,
   root_id                        int,
   left_id                        int,
   right_id                       int,
   order_id                       int,
   level_id                       int,
   is_enabled                     smallint,
   is_static                      smallint,
   access_key                     char(1)                       default NULL,
   rel                            varchar(16)                   default NULL,   
   primary key (section_id),
   key AK_key_root_id (root_id),
   key AK_key_order_id (order_id),
   key AK_key_left_id (left_id),
   key AK_key_right_id (right_id),
   key AK_id_root_l_r (section_id, root_id, left_id, right_id),
   key AK_key_level_id (level_id)
);

/*==============================================================*/
/* Table: category                                              */
/*==============================================================*/
create table if not exists category (
  category_id int(11) NOT NULL default '0',
  label varchar(32) default NULL,
  perms varchar(16) default NULL,
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
