-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL 
-- ==========================================================================

-- Schema for /modules/user


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

-- ==============================================================
--  Table: login                                                 
-- ==============================================================
create table login 
(
   login_id             INT4                 not null,
   usr_id               INT4                 null,
   date_time            TIMESTAMP            null,
   remote_ip            VARCHAR(16)          null,
   constraint PK_LOGIN primary key (login_id)
);


-- ==============================================================
--  Index: usr_login_fk                                          
-- ==============================================================
create  index usr_login_fk on login 
(
   usr_id
);


-- ==============================================================
--  Table: preference                                            
-- ==============================================================
create table preference 
(
   preference_id        INT4                 not null,
   name                 VARCHAR(128)         null,
   default_value        VARCHAR(128)         null,
   constraint PK_PREFERENCE primary key (preference_id)
);

-- ==============================================================
--  Table: organization                                          
-- ==============================================================
create table organisation 
(
   organisation_id int4 NOT NULL default 0,
   parent_id int4 NOT NULL default 0,
   root_id int4 NOT NULL default 0,
   left_id int4 NOT NULL default 0,
   right_id int4 NOT NULL default 0,
   order_id int4 NOT NULL default 0,
   level_id int4 NOT NULL default 0,
   role_id int4 NOT NULL default 0,
   organisation_type_id int4 NOT NULL default 0,
   name varchar(128) default NULL,
   description text,
   addr_1 varchar(128) NOT NULL default '',
   addr_2 varchar(128) default NULL,
   addr_3 varchar(128) default NULL,
   city varchar(32) NOT NULL default '',
   region varchar(32) default NULL,
   country varchar(32) default NULL,
   post_code varchar(16) default NULL,
   telephone varchar(32) default NULL,
   website varchar(128) default NULL,
   email varchar(128) default NULL,
   date_created timestamp default NULL,
   created_by int4 default NULL,
   last_updated timestamp default NULL,
   updated_by int4 default NULL,
   constraint PK_ORGANISATION_ID primary key (organisation_id)
);

-- ==============================================================
--  Table: organisation_type                                     
-- ==============================================================
create table organisation_type 
(
   organisation_type_id int4 NOT NULL default 0,
   name varchar(64) default NULL,
   primary key (organisation_type_id)
);


-- ==============================================================
--  Table: permission                                            
-- ==============================================================
create table permission 
(
   permission_id int4 NOT NULL default 0,
   name varchar(255) default NULL,
   description text,
   module_id int4 NOT NULL default 0,
   constraint pk_permission_id primary key  (permission_id)
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
create table role 
(
   role_id int NOT NULL default -1,
   name varchar(255) default NULL,
   description text,
   date_created timestamp default NULL,
   created_by int4 default NULL,
   last_updated timestamp default NULL,
   updated_by int4 default NULL,	  
   CONSTRAINT PK_ROLE_ID PRIMARY KEY (role_id)
);

-- ==============================================================
--  Table: role_permission                                       
-- ==============================================================
CREATE TABLE role_permission 
(
   role_permission_id int4 NOT NULL default 0,
   role_id int4 NOT NULL default 0,
   permission_id int4 NOT NULL default 0,
   CONSTRAINT PK_ROLE_PERMISSION_ID PRIMARY KEY (role_permission_id)
);

-- ==============================================================
--  Index: permission_id                                         
-- ==============================================================
create  index permission_id on role_permission 
(
   permission_id
);

-- ==============================================================
--  Index: role_id                                               
-- ==============================================================
create  index role_id on role_permission 
(
   role_id
);

-- ==============================================================
--  Table: user_preference                                       
-- ==============================================================
create table user_preference 
(
   user_preference_id   INT4                 not null,
   usr_id               INT8                 not null default 0,
   preference_id        INT4                 not null,
   value                VARCHAR(128)         null,
   constraint PK_USER_PREFERENCE primary key (user_preference_id)
);

-- ==============================================================
--  Index: usr_user_preferences_fk                               
-- ==============================================================
create  index usr_user_preferences_fk on user_preference 
(
   usr_id
);

-- ==============================================================
--  Index: preference_user_preference_fk                          
-- ==============================================================
create  index preference_user_preference_fk on user_preference 
(
   preference_id
);

-- ==============================================================
--  Table: org_preference                                       
-- ==============================================================
create table org_preference 
(
   org_preference_id   INT4                 not null,
   organisation_id      INT8                 not null default 0,
   preference_id        INT4                 not null,
   value                VARCHAR(128)         null,
   constraint PK_ORG_PREFERENCE primary key (org_preference_id)
);

-- ==============================================================
--  Index: organisation_org_preference_fk                               
-- ==============================================================
create  index organisation_org_preference_fk on org_preference 
(
   organisation_id
);

-- ==============================================================
--  Index: preference_org_preference_fk                          
-- ==============================================================
create  index preference_org_preference_fk on org_preference 
(
   preference_id
);

-- ==============================================================
--  Table: usr                                                   
-- ==============================================================
create table usr 
(
   usr_id               INT4                 not null,
   organisation_id      INT4                 not null,
   role_id              INT4                 not null,
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
   country              VARCHAR(64)          null,
   post_code            VARCHAR(16)          null,
   is_email_public      INT2                 null,
   is_acct_active       INT2                 null,
   security_question    INT2                 null,
   security_answer      VARCHAR(128)         null,
   date_created         TIMESTAMP            null,
   created_by           INT4                 null,
   last_updated         TIMESTAMP            null,
   updated_by           INT4                 null,
   constraint PK_USR primary key (usr_id)
);

-- ==============================================================
--  Table: user_permission
-- ==============================================================
CREATE TABLE user_permission 
(
   user_permission_id int4 NOT NULL default 0,
   usr_id int4 NOT NULL default 0,
   permission_id int4 NOT NULL default 0,
   CONSTRAINT PK_USER_PERMISSION_ID PRIMARY KEY (user_permission_id)
);

-- ==============================================================
--  Index: usr_id
-- ==============================================================
create index usr_id on user_permission 
(
   usr_id
);

-- ==============================================================
--  Index: user_permission_id
-- ==============================================================
create index user_permission_id on user_permission 
(
   permission_id
);

COMMIT;
