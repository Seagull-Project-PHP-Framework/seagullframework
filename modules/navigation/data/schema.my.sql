/*==============================================================*/
/* Table: section                                               */
/*==============================================================*/
create table if not exists section
(
   section_id                     int                            not null,
   title                          varchar(32),
   resource_uri                   varchar(128),
   perms                          varchar(32),
   trans_id                       int,
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
/* Table: uri_alias                                             */
/*==============================================================*/
CREATE TABLE uri_alias (
  uri_alias_id int(11) unsigned NOT NULL default '0',
  uri_alias varchar(255) default NULL,
  section_id int(11) default NULL,
  title varchar(255) default NULL,
  keywords text,
  description text,
  PRIMARY KEY  (uri_alias_id),
  UNIQUE KEY uri_alias (uri_alias)
) ;
