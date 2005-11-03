
/*==============================================================*/
/* DBMS name:      MySQL 4.0                                    */
/* Created on:     2004-04-05 01:05:58                          */
/*==============================================================*/

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
/* Table: module                                                 */
/*==============================================================*/
create table if not exists module (
  module_id int(11) not null,
  is_configurable smallint(1),
  name varchar(255),
  title varchar(255),
  description text,
  admin_uri varchar(255),
  icon varchar(255),
  primary key  (module_id)
);