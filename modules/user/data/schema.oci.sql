-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00

-- ==============================================================
--  Table: login                                                 
-- ==============================================================
create table login (
login_id             NUMBER(10)                 not null,
usr_id               NUMBER(10)                 null,
date_time            DATE            null,
remote_ip            VARCHAR(16)          null,
constraint PK_LOGIN primary key (login_id)
);

-- ==============================================================
--  sequence login_seq
-- ==============================================================

create sequence login_seq;

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
preference_id        NUMBER(10)                 not null,
name                 VARCHAR(128)         null,
default_value        VARCHAR(128)         null,
constraint PK_PREFERENCE primary key (preference_id)
);

-- ==============================================================
--  sequence preference_seq
-- ==============================================================

create sequence preference_seq;

-- ==============================================================
--  Table: organisation                                          
-- ==============================================================
CREATE TABLE organisation (
organisation_id NUMBER(10) NOT NULL,
parent_id NUMBER(10) default 0 NOT NULL,
root_id NUMBER(10) default 0 NOT NULL,
left_id NUMBER(10) default 0 NOT NULL,
right_id NUMBER(10) default 0 NOT NULL,
order_id NUMBER(10) default 0 NOT NULL,
level_id NUMBER(10) default 0 NOT NULL,
role_id NUMBER(10) default 0 NOT NULL,
organisation_type_id NUMBER(10) default 0 NOT NULL,
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
date_created DATE default NULL,
created_by NUMBER(10) default NULL,
last_updated DATE default NULL,
updated_by NUMBER(10) default NULL,
constraint PK_ORGANISATION_ID primary key (organisation_id)
);

-- ==============================================================
--  sequence organisation_seq
-- ==============================================================

create sequence organisation_seq;

-- ==============================================================
--  Table: organisation_type                                     
-- ==============================================================
CREATE TABLE organisation_type (
organisation_type_id NUMBER(10) default 0 NOT NULL,
name varchar(64) default NULL,
primary key (organisation_type_id)
);

-- ==============================================================
--  sequence organisation_type_seq
-- ==============================================================

create sequence organisation_type_seq;

-- ==============================================================
--  Table: permission                                            
-- ==============================================================
CREATE TABLE permission (
permission_id NUMBER(10) NOT NULL,
name varchar(255) default NULL,
description CLOB,
module_id NUMBER(10) default 0 NOT NULL,
CONSTRAINT PK_PERMISSION_ID PRIMARY KEY  (permission_id)
);

-- ==============================================================
-- sequence permission_seq
-- ==============================================================

create sequence permission_seq;

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
role_id NUMBER(10) NOT NULL,
name varchar(255) default NULL,
description long,
date_created DATE default NULL,
created_by NUMBER(10) default NULL,
last_updated DATE default NULL,
updated_by NUMBER(10) default NULL,	  
CONSTRAINT PK_ROLE_ID PRIMARY KEY (role_id)
);

-- ==============================================================
--  sequence role_seq
-- ==============================================================

create sequence role_seq;

-- ==============================================================
--  Table: role_permission                                       
-- ==============================================================
CREATE TABLE role_permission (
role_permission_id NUMBER(10) NOT NULL,
role_id NUMBER(10) default 0 NOT NULL,
permission_id NUMBER(10) default 0 NOT NULL,
CONSTRAINT PK_ROLE_PERMISSION_ID PRIMARY KEY (role_permission_id)
);

-- ==============================================================
--  sequence role_permission_seq
-- ==============================================================

create sequence role_permission_seq;

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
user_preference_id   NUMBER(10)                 not null,
usr_id               NUMBER(10)  default 0      not null,
preference_id        NUMBER(10)                 not null,
value                VARCHAR(128)         null,
constraint PK_USER_PREFERENCE primary key (user_preference_id)
);

-- ==============================================================
--  sequence user_preference_seq
-- ==============================================================

create sequence user_preference_seq;

-- ==============================================================
--  Index: usr_user_preferences_fk                               
-- ==============================================================
create  index usr_user_preferences_fk on user_preference (
usr_id
);

-- ==============================================================
--  Index: preference_user_preference_fk                          
-- ==============================================================
create  index preference_user_preference_fk on user_preference (
preference_id
);

-- ==============================================================
--  Table: org_preference                                       
-- ==============================================================
create table org_preference (
org_preference_id   NUMBER(10)                 not null,
organisation_id      NUMBER(10)                 default 0 not null,
preference_id        NUMBER(10)                 not null,
value                VARCHAR(128)         null,
constraint PK_ORG_PREFERENCE primary key (org_preference_id)
);

-- ==============================================================
--  sequence org_preference_seq
-- ==============================================================

create sequence org_preference_seq;

-- ==============================================================
--  Index: organisation_org_preference_fk                               
-- ==============================================================
create  index organisation_org_preference_fk on org_preference (
organisation_id
);

-- ==============================================================
--  Index: preference_org_preference_fk                          
-- ==============================================================
create  index preference_org_preference_fk on org_preference (
preference_id
);

-- ==============================================================
--  Table: usr                                                   
-- ==============================================================
create table usr (
usr_id               NUMBER(10)                 not null,
organisation_id      NUMBER(10)                 null,
role_id              NUMBER(10)                 not null,
username             VARCHAR(64)          null,
passwd               VARCHAR(32)          null,
first_name           VARCHAR(128)         null,
last_name            VARCHAR(128)         null,
telephone            VARCHAR(16)          null,
mobile               VARCHAR(16)          null,
email                VARCHAR(128)         null,
addr_1               VARCHAR(128)         null,
addr_2               VARCHAR(128)         null,
addr_3               VARCHAR(128)         null,
city                 VARCHAR(64)          null,
region               VARCHAR(32)          null,
country              CHAR(2)              null,
post_code            VARCHAR(16)          null,
is_email_public      NUMBER(5)                 null,
is_acct_active       NUMBER(5)                 null,
security_question    NUMBER(5)                 null,
security_answer      VARCHAR(128)         null,
date_created         DATE            null,
created_by           NUMBER(10)                 null,
last_updated         DATE            null,
updated_by           NUMBER(10)                 null,
constraint PK_USR primary key (usr_id)
);

-- ==============================================================
--  sequence usr_seq
-- ==============================================================

create sequence usr_seq;

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
user_permission_id NUMBER(10) NOT NULL,
usr_id NUMBER(10) default 0 NOT NULL,
permission_id NUMBER(10) default 0 NOT NULL,
CONSTRAINT PK_USER_PERMISSION_ID PRIMARY KEY (user_permission_id)
);

-- ==============================================================
--  sequence user_permission_seq
-- ==============================================================

create sequence user_permission_seq;

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
