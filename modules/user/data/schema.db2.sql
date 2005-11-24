-- ==============================================================
--  DBMS name:      IBM DB2                              
--  Created on:     2005-11-17 10:32:00

-- ==============================================================
--  Table: login                                                 
-- ==============================================================
create table login (
login_id             INTEGER                 not null,
usr_id               INTEGER                 ,
date_time            TIMESTAMP            ,
remote_ip            VARCHAR(16)          ,
constraint PK_LOGIN primary key (login_id)
);

-- ==============================================================
--  Index: usr_login_fk                                          
-- ==============================================================
create  index usr_login_fk on login (
usr_id
);

-- ==============================================================
--  Table: preference                                            
-- ==============================================================
create table preference (
preference_id        INTEGER                 not null,
name                 VARCHAR(128)         ,
default_value        VARCHAR(128)         ,
constraint PK_PREFERENCE primary key (preference_id)
);

-- ==============================================================
--  Table: organization                                          
-- ==============================================================
CREATE TABLE organisation (
organisation_id INTEGER NOT NULL,
parent_id INTEGER default 0 NOT NULL,
root_id INTEGER default 0 NOT NULL,
left_id INTEGER default 0 NOT NULL,
right_id INTEGER default 0 NOT NULL,
order_id INTEGER default 0 NOT NULL,
level_id INTEGER default 0 NOT NULL,
role_id INTEGER default 0 NOT NULL,
organisation_type_id INTEGER default 0 NOT NULL,
name varchar(128) default NULL,
description CLOB,
addr_1 varchar(128) default '' NOT NULL,
addr_2 varchar(128) default NULL,
addr_3 varchar(128) default NULL,
city varchar(32) default '' NOT NULL,
region varchar(32) default NULL,
country char(2) default NULL,
post_code varchar(16) default NULL,
telephone varchar(32) default NULL,
website varchar(128) default NULL,
email varchar(128) default NULL,
date_created TIMESTAMP default NULL,
created_by INTEGER default NULL,
last_updated TIMESTAMP default NULL,
updated_by INTEGER default NULL,
constraint PK_ORGANISATION_ID primary key (organisation_id)
);

-- ==============================================================
--  Table: organisation_type                                     
-- ==============================================================
CREATE TABLE organisation_type (
organisation_type_id INTEGER default 0 NOT NULL,
name varchar(64) default NULL,
primary key (organisation_type_id)
);

-- ==============================================================
--  Table: permission                                            
-- ==============================================================
CREATE TABLE permission (
permission_id INTEGER NOT NULL,
name varchar(255) default NULL,
description CLOB,
module_id INTEGER default 0 NOT NULL,
CONSTRAINT PK_PERMISSION_ID PRIMARY KEY  (permission_id)
);

-- ==============================================================
--  Index: perm_name                                            
-- ==============================================================
CREATE UNIQUE INDEX perm_name ON permission (
name
);

-- ==============================================================
--  Table: role                                                  
-- ==============================================================
CREATE TABLE role (
role_id INTEGER NOT NULL,
name varchar(255) default NULL,
description long varchar,
date_created TIMESTAMP default NULL,
created_by INTEGER default NULL,
last_updated TIMESTAMP default NULL,
updated_by INTEGER default NULL,	  
CONSTRAINT PK_ROLE_ID PRIMARY KEY (role_id)
);

-- ==============================================================
--  Table: role_permission                                       
-- ==============================================================
CREATE TABLE role_permission (
role_permission_id INTEGER NOT NULL,
role_id INTEGER default 0 NOT NULL,
permission_id INTEGER default 0 NOT NULL,
CONSTRAINT PK_ROLE_PERM_ID PRIMARY KEY (role_permission_id)
);

-- ==============================================================
--  Index: permission_id                                         
-- ==============================================================
create  index permission_id on role_permission (
permission_id
);

-- ==============================================================
--  Index: role_id                                               
-- ==============================================================
create  index role_id on role_permission (
role_id
);


-- ==============================================================
--  Table: user_preference                                       
-- ==============================================================
create table user_preference (
user_preference_id   INTEGER                 not null,
usr_id               INTEGER  default 0      not null,
preference_id        INTEGER                 not null,
value                VARCHAR(128)         ,
constraint PK_USER_PREFERENCE primary key (user_preference_id)
);

-- ==============================================================
--  Index: usr_user_preferences_fk                               
-- ==============================================================
create  index usr_user_pref_fk on user_preference (
usr_id
);

-- ==============================================================
--  Index: preference_user_preference_fk                          
-- ==============================================================
create  index pref_user_pref_fk on user_preference (
preference_id
);

-- ==============================================================
--  Table: org_preference                                       
-- ==============================================================
create table org_preference (
org_preference_id   INTEGER                 not null,
organisation_id      INTEGER                 default 0 not null,
preference_id        INTEGER                 not null,
value                VARCHAR(128)         ,
constraint PK_ORG_PREFERENCE primary key (org_preference_id)
);

-- ==============================================================
--  Index: organisation_org_preference_fk                               
-- ==============================================================
create  index org_org_pref_fk on org_preference (
organisation_id
);

-- ==============================================================
--  Index: preference_org_preference_fk                          
-- ==============================================================
create  index pref_org_pref_fk on org_preference (
preference_id
);

-- ==============================================================
--  Table: usr                                                   
-- ==============================================================
create table usr (
usr_id               INTEGER                 not null,
organisation_id      INTEGER                 ,
role_id              INTEGER                 not null,
username             VARCHAR(64)          ,
passwd               VARCHAR(32)          ,
first_name           VARCHAR(128)         ,
last_name            VARCHAR(128)         ,
telephone            VARCHAR(16)          ,
mobile               VARCHAR(16)          ,
email                VARCHAR(128)         ,
addr_1               VARCHAR(128)         ,
addr_2               VARCHAR(128)         ,
addr_3               VARCHAR(128)         ,
city                 VARCHAR(64)          ,
region               VARCHAR(32)          ,
country              CHAR(2)              ,
post_code            VARCHAR(16)          ,
is_email_public      SMALLINT                 ,
is_acct_active       SMALLINT                 ,
security_question    SMALLINT                 ,
security_answer      VARCHAR(128)         ,
date_created         TIMESTAMP            ,
created_by           INTEGER                 ,
last_updated         TIMESTAMP            ,
updated_by           INTEGER                 ,
constraint PK_USR primary key (usr_id)
);

-- ==============================================================
--  Index: usr_username                                            
-- ==============================================================
CREATE UNIQUE INDEX usr_username ON usr (
username
);

-- ==============================================================
--  Index: usr_email                                            
-- ==============================================================
CREATE UNIQUE INDEX usr_email ON usr (
email
);

-- ==============================================================
--  Table: user_permission
-- ==============================================================
CREATE TABLE user_permission (
user_permission_id INTEGER NOT NULL,
usr_id INTEGER default 0 NOT NULL,
permission_id INTEGER default 0 NOT NULL,
CONSTRAINT PK_USER_PERM_ID PRIMARY KEY (user_permission_id)
);

-- ==============================================================
--  Index: usr_id
-- ==============================================================
create index usr_id on user_permission (
usr_id
);

-- ==============================================================
--  Index: usr_permission_id
-- ==============================================================
create index user_permission_id on user_permission (
permission_id
);
