-- The following schema will load user related tables with correct FK constraints
-- however mysql 4.0.x and 4.1.x seem to have a bug where deleting a record with a child
-- constraint gives an FK violation error.  The correct behaviour is when a user record
-- is deleted where usr_id = 2, all related records from user_permission and user_preference
-- should also be deleted on the cascade.  MySQL appears to misinterpret this and will only
-- delete the user record when wrapped in SET_FOREIGN_KEY_CHECK=0 calls.  Not surprisingly,
-- this disables the cascade behaviour defeating the original purpose of using cascades.
--
-- Unless someone discovers a workaround, FK integrity will be abandoned in the MySQL version
-- until this bug is fixed.

/*==============================================================*/
/* Table: usr                                                   */
/*==============================================================*/
CREATE TABLE if not exists usr (
  usr_id int(11) NOT NULL default '0',
  organisation_id int(11) NOT NULL default '0',
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
  PRIMARY KEY  (usr_id),
  UNIQUE KEY (email, username)
) TYPE=InnoDB;

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
/* Table: user_permission                                       */
/*==============================================================*/
CREATE TABLE if not exists user_permission (
  user_permission_id int(11) NOT NULL default '0',
  usr_id int(11) NOT NULL default '0',
  permission_id int(11) NOT NULL default '0',
  PRIMARY KEY  (user_permission_id),
  INDEX (usr_id, permission_id),
  FOREIGN KEY (usr_id) REFERENCES usr (usr_id)
    ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permission (permission_id)
    ON DELETE CASCADE
) TYPE=InnoDB;

/*==============================================================*/
/* Table: preference                                            */
/*==============================================================*/
create table if not exists preference
(
   preference_id                  int                            not null,
   name                           varchar(128),
   default_value                  varchar(128),
   primary key (preference_id)
)TYPE=InnoDB;


/*==============================================================*/
/* Table: user_preference                                       */
/*==============================================================*/
create table if not exists user_preference
(
   user_preference_id             int                            not null,
   usr_id                         int                            not null,
   preference_id                  int                            not null,
   value                          varchar(128),
   primary key (user_preference_id),
   INDEX (usr_id, preference_id),
   FOREIGN KEY (usr_id) REFERENCES usr (usr_id)
     ON DELETE CASCADE,
   FOREIGN KEY (preference_id) REFERENCES preference(preference_id)
     ON DELETE CASCADE
)TYPE=InnoDB;

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
  INDEX (permission_id, role_id),
  FOREIGN KEY (permission_id) REFERENCES permission (permission_id)
    ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES role (role_id)
    ON DELETE CASCADE
) TYPE=InnoDB;


/*==============================================================*/
/* Table: login                                                 */
/*==============================================================*/
create table if not exists login
(
   login_id                       int                            not null,
   usr_id                         int,
   date_time                      datetime,
   remote_ip                      varchar(16),
   primary key (login_id),
   INDEX (usr_id)
)TYPE=InnoDB;

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
) TYPE=InnoDB;

/*==============================================================*/
/* Table: org_preference                                        */
/*==============================================================*/
CREATE TABLE if not exists org_preference (
  org_preference_id int(11) NOT NULL default '0',
  organisation_id int(11) NOT NULL default '0',
  preference_id int(11) NOT NULL default '0',
  value varchar(128) default NULL,
  PRIMARY KEY  (org_preference_id),
  INDEX (organisation_id, preference_id),
  FOREIGN KEY (organisation_id) REFERENCES organisation (organisation_id)
    ON DELETE CASCADE,
  FOREIGN KEY (preference_id) REFERENCES preference (preference_id)
    ON DELETE CASCADE
) TYPE=InnoDB;

/*==============================================================*/
/* Table: organisation_type                                     */
/*==============================================================*/
CREATE TABLE if not exists organisation_type (
  organisation_type_id int(11) NOT NULL default '0',
  name varchar(64) default NULL,
  PRIMARY KEY  (organisation_type_id)
) TYPE=MyISAM;