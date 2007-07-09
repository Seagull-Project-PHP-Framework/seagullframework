-- Last edited: Antonio J. Garcia 2007-04-21
-- Schema for /modules/user

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
--  sequence login_seq
-- ==============================================================

create sequence login_seq;

-- ==============================================================
--  Index: usr_login_fk
-- ==============================================================
create index usr_login_fk on login
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
--  sequence preference_seq
-- ==============================================================

create sequence preference_seq;

-- ==============================================================
--  Table: organisation
-- ==============================================================
create table organisation (
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
   country char(2) default NULL,
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
--  sequence organisation_seq
-- ==============================================================

create sequence organisation_seq;

-- ==============================================================
--  Table: organisation_type
-- ==============================================================
create table organisation_type (
   organisation_type_id int4 NOT NULL default 0,
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
create table permission (
   permission_id int4 NOT NULL default 0,
   name varchar(255) default NULL,
   description text,
   module_id int4 NOT NULL default 0,
   constraint pk_permission_id primary key  (permission_id)
);

-- ==============================================================
-- sequence permission_seq
-- ==============================================================

create sequence permission_seq;

-- ==============================================================
--  Index: perm_name
-- ==============================================================
create unique index perm_name ON permission (
   name
);

-- ==============================================================
--  Table: role
-- ==============================================================
create table role (
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
--  sequence role_seq
-- ==============================================================

create sequence role_seq;

-- ==============================================================
--  Table: role_permission
-- ==============================================================
create table role_permission (
   role_permission_id int4 NOT NULL default 0,
   role_id int4 NOT NULL default 0,
   permission_id int4 NOT NULL default 0,
   CONSTRAINT PK_ROLE_PERMISSION_ID PRIMARY KEY (role_permission_id)
);

-- ==============================================================
--  sequence role_permission_seq
-- ==============================================================

create sequence role_permission_seq;

-- ==============================================================
--  Index: permission_id
-- ==============================================================
create index permission_id on role_permission
(
   permission_id
);

-- ==============================================================
--  Index: role_id
-- ==============================================================
create index role_id on role_permission
(
   role_id
);

-- ==============================================================
--  Table: user_preference
-- ==============================================================
create table user_preference
(
   user_preference_id   INT4                 not null,
   usr_id               INT4                 not null default 0,
   preference_id        INT4                 not null,
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
create index usr_user_preferences_fk on user_preference
(
   usr_id
);

-- ==============================================================
--  Index: preference_user_preference_fk
-- ==============================================================
create index preference_user_preference_fk on user_preference
(
   preference_id
);

-- ==============================================================
--  Table: org_preference
-- ==============================================================
create table org_preference (
   org_preference_id    INT4                 not null,
   organisation_id      INT4                 not null default 0,
   preference_id        INT4                 not null,
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
create index organisation_org_preference_fk on org_preference
(
   organisation_id
);

-- ==============================================================
--  Index: preference_org_preference_fk
-- ==============================================================
create index preference_org_preference_fk on org_preference
(
   preference_id
);

-- ==============================================================
--  Table: usr
-- ==============================================================
create table usr (
   usr_id               INT4                 not null,
   organisation_id      INT4                 null,
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
   country              CHAR(2)              null,
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
--  sequence usr_seq
-- ==============================================================

create sequence usr_seq;

-- ==============================================================
--  Index: usr_email
-- ==============================================================
/* we'll see if dbdo fixes this problem */
-- create unique index usr_email ON usr (
--    email
-- );

-- ==============================================================
--  Index: usr_username
-- ==============================================================
-- create unique index usr_username ON usr (
--    username
-- );

-- ==============================================================
--  Table: user_permission
-- ==============================================================
create table user_permission (
   user_permission_id int4 NOT NULL default 0,
   usr_id int4 NOT NULL default 0,
   permission_id int4 NOT NULL default 0,
   CONSTRAINT PK_USER_PERMISSION_ID PRIMARY KEY (user_permission_id)
);

-- ==============================================================
--  sequence user_permission_seq
-- ==============================================================

create sequence user_permission_seq;

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
--================================================================
-- Table: user_cookie
--================================================================
CREATE TABLE user_cookie (
    usr_id                  integer             NOT NULL,
    cookie_name             varchar(32)         NOT NULL,
    login_time              timestamp            NOT NULL
);
-- ==============================================================
--  Index: user_cookie_usr_id
-- ==============================================================
create index user_cookie_usr_id on user_cookie
(
   usr_id
);
-- ==============================================================
--  Index: user_cookie_cookie_name
-- ==============================================================
create index user_cookie_cookie_name on user_cookie
(
   cookie_name
);
COMMIT;
