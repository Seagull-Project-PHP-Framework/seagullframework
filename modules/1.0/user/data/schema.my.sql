/*==============================================================*/
/* Table: login                                                 */
/*==============================================================*/
create table if not exists login
(
   login_id                       int                            not null,
   usr_id                         int,
   date_time                      datetime,
   remote_ip                      varchar(16),
   primary key (login_id)
);

/*==============================================================*/
/* Index: usr_login_fk                                          */
/*==============================================================*/
create index usr_login_fk on login
(
   usr_id
);

/*==============================================================*/
/* Table: preference                                            */
/*==============================================================*/
create table if not exists preference
(
   preference_id                  int                            not null,
   name                           varchar(128),
   default_value                  varchar(128),
   primary key (preference_id)
);

/*==============================================================*/
/* Table: organisation                                          */
/*==============================================================*/
CREATE TABLE if not exists organisation (
  organisation_id int(11) NOT NULL default '0',
  parent_id int(11) NOT NULL default '0',
  root_id int(11) NOT NULL default '0',
  left_id int(11) NOT NULL default '0',
  right_id int(11) NOT NULL default '0',
  order_id int(11) NOT NULL default '0',
  level_id int(11) NOT NULL default '0',
  role_id int(11) NOT NULL default '0',
  organisation_type_id int(11) NOT NULL default '0',
  name varchar(128) default NULL,
  description text,
  addr_1 varchar(128) NOT NULL default '',
  addr_2 varchar(128) default NULL,
  addr_3 varchar(128) default NULL,
  city varchar(32) NOT NULL default '',
  region varchar(32) default NULL,
  country char(2) default NULL,
  post_code varchar(16) default NULL,
  telephone varchar(32) default NULL,
  website varchar(128) default NULL,
  email varchar(128) default NULL,
  date_created datetime default NULL,
  created_by int(11) default NULL,
  last_updated datetime default NULL,
  updated_by int(11) default NULL,
  PRIMARY KEY  (organisation_id)
) TYPE=MyISAM;

/*==============================================================*/
/* Table: organisation_type                                     */
/*==============================================================*/
CREATE TABLE if not exists organisation_type (
  organisation_type_id int(11) NOT NULL default '0',
  name varchar(64) default NULL,
  PRIMARY KEY  (organisation_type_id)
) TYPE=MyISAM;

/*==============================================================*/
/* Table: permission                                            */
/*==============================================================*/
CREATE TABLE if not exists permission (
  permission_id int(11) NOT NULL default '0',
  name varchar(255) default NULL,
  description text,
  module_id int(11) NOT NULL default '0',
  PRIMARY KEY  (permission_id),
  UNIQUE KEY name (name)
) TYPE=InnoDB;

/*==============================================================*/
/* Table: role                                                  */
/*==============================================================*/
CREATE TABLE if not exists role (
  role_id int(11) NOT NULL default '-1',
  name varchar(255) default NULL,
  description text,
  date_created datetime default NULL,
  created_by int(11) default NULL,
  last_updated datetime default NULL,
  updated_by int(11) default NULL,
  PRIMARY KEY  (role_id)
) TYPE=InnoDB;

/*==============================================================*/
/* Table: role_permission                                       */
/*==============================================================*/
CREATE TABLE if not exists role_permission (
  role_permission_id int(11) NOT NULL default '0',
  role_id int(11) NOT NULL default '0',
  permission_id int(11) NOT NULL default '0',
  PRIMARY KEY  (role_permission_id),
  KEY permission_id (permission_id),
  KEY role_id (role_id)
) TYPE=InnoDB;

/*==============================================================*/
/* Table: user_preference                                       */
/*==============================================================*/
create table if not exists user_preference
(
   user_preference_id             int                            not null,
   usr_id                         int                            not null,
   preference_id                  int                            not null,
   value                          varchar(128),
   primary key (user_preference_id)
);

/*==============================================================*/
/* Index: usr_user_preference_fk                               */
/*==============================================================*/
create index usr_user_preference_fk on user_preference
(
   usr_id
);

/*==============================================================*/
/* Index: preference_user_preference_fk                          */
/*==============================================================*/
create index preference_user_preference_fk on user_preference
(
   preference_id
);

/*==============================================================*/
/* Table: org_preference                                        */
/*==============================================================*/
CREATE TABLE if not exists org_preference (
  org_preference_id int(11) NOT NULL default '0',
  organisation_id int(11) NOT NULL default '0',
  preference_id int(11) NOT NULL default '0',
  value varchar(128) default NULL,
  PRIMARY KEY  (org_preference_id)
) TYPE=MyISAM;

/*==============================================================*/
/* Index: usr_user_preference_fk                               */
/*==============================================================*/
create index organisation_org_preference_fk on org_preference
(
   organisation_id
);

/*==============================================================*/
/* Index: preferene_org_preference_fk                          */
/*==============================================================*/
create index preference_org_preference_fk on org_preference
(
   preference_id
);


/*==============================================================*/
/* Table: usr                                                   */
/*==============================================================*/
CREATE TABLE if not exists usr (
  usr_id int(11) NOT NULL default '0',
  organisation_id int(11) NULL default '0',
  role_id int(11) NOT NULL default '0',
  username varchar(64) default NULL,
  passwd varchar(32) default NULL,
  first_name varchar(128) default NULL,
  last_name varchar(128) default NULL,
  telephone varchar(16) default NULL,
  mobile varchar(16) default NULL,
  email varchar(128) default NULL,
  addr_1 varchar(128) default NULL,
  addr_2 varchar(128) default NULL,
  addr_3 varchar(128) default NULL,
  city varchar(64) default NULL,
  region varchar(32) default NULL,
  country char(2) default NULL,
  post_code varchar(16) default NULL,
  is_email_public smallint(6) default NULL,
  is_acct_active smallint(6) default NULL,
  security_question smallint(6) default NULL,
  security_answer varchar(128) default NULL,
  date_created datetime default NULL,
  created_by int(11) default NULL,
  last_updated datetime default NULL,
  updated_by int(11) default NULL,
  PRIMARY KEY  (usr_id)
) TYPE=InnoDB;

/* we'll see if dbdo fixes this problem */
-- CREATE UNIQUE INDEX usr_email ON usr (
--    email
-- );
--
-- CREATE UNIQUE INDEX usr_username ON usr (
--    username
-- );

/*==============================================================*/
/* Table: user_permission                                       */
/*==============================================================*/
CREATE TABLE if not exists user_permission (
  user_permission_id int(11) NOT NULL default '0',
  usr_id int(11) NOT NULL default '0',
  permission_id int(11) NOT NULL default '0',
  PRIMARY KEY  (user_permission_id),
  KEY usr_id (usr_id),
  KEY permission_id (permission_id)
) TYPE=InnoDB;

/*==============================================================*/
/* Table: user_cookie                                           */
/*==============================================================*/
CREATE TABLE `user_cookie` (
    `usr_id`                  int(11)             NOT NULL,
    `cookie_name`             varchar(32)         NOT NULL,
    `login_time`              datetime            NOT NULL,

    KEY (usr_id),
    KEY (cookie_name)
);