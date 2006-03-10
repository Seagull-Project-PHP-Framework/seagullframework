-- ==========================================================================
-- Seagull PHP Framework: Default Data for MaxDB
-- ==========================================================================

-- Schema for /modules/user


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
-- ==============================================================
--  Table: login
-- ==============================================================
CREATE TABLE login
(
   login_id             INTEGER                 NOT  NULL,
   usr_id               INTEGER                 NULL,
   date_time            TIMESTAMP            NULL,
   remote_ip            VARCHAR(16)          NULL
);

-- ==============================================================
--  Table: preference
-- ==============================================================
CREATE TABLE preference
(
   preference_id        INTEGER                 NOT  NULL,
   name                 VARCHAR(128)         NULL,
   default_value        VARCHAR(128)         NULL
);

-- ==============================================================
--  Table: organization
-- ==============================================================
CREATE TABLE organisation
(
   organisation_id INTEGER NOT NULL DEFAULT 0,
   parent_id INTEGER NOT NULL DEFAULT 0,
   root_id INTEGER NOT NULL DEFAULT 0,
   left_id INTEGER NOT NULL DEFAULT 0,
   right_id INTEGER NOT NULL DEFAULT 0,
   order_id INTEGER NOT NULL DEFAULT 0,
   level_id INTEGER NOT NULL DEFAULT 0,
   role_id INTEGER NOT NULL DEFAULT -1,
   organisation_type_id INTEGER NOT NULL DEFAULT 0,
   name VARCHAR(128) DEFAULT NULL,
   description LONG,
   addr_1 VARCHAR(128) NOT NULL DEFAULT '',
   addr_2 VARCHAR(128) DEFAULT NULL,
   addr_3 VARCHAR(128) DEFAULT NULL,
   city VARCHAR(32) NOT NULL DEFAULT '',
   region VARCHAR(32) DEFAULT NULL,
   country char(2) DEFAULT NULL,
   post_code VARCHAR(16) DEFAULT NULL,
   telephone VARCHAR(32) DEFAULT NULL,
   website VARCHAR(128) DEFAULT NULL,
   email VARCHAR(128) DEFAULT NULL,
   date_created timestamp DEFAULT NULL,
   created_by INTEGER DEFAULT NULL,
   last_updated timestamp DEFAULT NULL,
   updated_by INTEGER DEFAULT NULL
);

-- ==============================================================
--  Table: organisation_type
-- ==============================================================
CREATE TABLE organisation_type
(
   organisation_type_id INTEGER NOT NULL DEFAULT 0,
   name VARCHAR(64) DEFAULT NULL
);


-- ==============================================================
--  Table: permission
-- ==============================================================
CREATE TABLE permission
(
   permission_id INTEGER NOT NULL DEFAULT 0,
   name VARCHAR(255) DEFAULT NULL,
   description LONG,
   module_id INTEGER NOT NULL DEFAULT 0
);

-- ==============================================================
--  Table: role
-- ==============================================================
CREATE TABLE role
(
   role_id INTEGER NOT NULL DEFAULT -1,
   name VARCHAR(255) DEFAULT NULL,
   description LONG,
   date_created timestamp DEFAULT NULL,
   created_by INTEGER DEFAULT NULL,
   last_updated timestamp DEFAULT NULL,
   updated_by INTEGER DEFAULT NULL
);

-- ==============================================================
--  Table: user_preference
-- ==============================================================
CREATE TABLE user_preference
(
   user_preference_id   INTEGER                 NOT  NULL,
   usr_id               INTEGER                 NOT  NULL DEFAULT 0,
   preference_id        INTEGER                 NOT  NULL,
   pref_value           VARCHAR(128)         NULL
);

-- ==============================================================
--  Table: role_permission
-- ==============================================================
CREATE TABLE role_permission
(
   role_permission_id INTEGER NOT NULL DEFAULT 0,
   role_id INTEGER NOT NULL DEFAULT -1,
   permission_id INTEGER NOT NULL DEFAULT 0
);

-- ==============================================================
--  Table: org_preference
-- ==============================================================
CREATE TABLE org_preference
(
   org_preference_id   INTEGER                 NOT  NULL,
   organisation_id      INTEGER                 NOT  NULL DEFAULT 0,
   preference_id        INTEGER                 NOT  NULL,
   pref_value           VARCHAR(128)         NULL
);

-- ==============================================================
--  Table: usr
-- ==============================================================
CREATE TABLE usr
(
   usr_id               INTEGER                 NOT  NULL,
   organisation_id      INTEGER                 NULL,
   role_id 				INTEGER 			NOT NULL DEFAULT -1,
   username             VARCHAR(64)          NULL,
   passwd               VARCHAR(32)          NULL,
   first_name           VARCHAR(128)         NULL,
   last_name            VARCHAR(128)         NULL,
   telephone            VARCHAR(16)          NULL,
   mobile               VARCHAR(16)          NULL,
   email                VARCHAR(128)         NULL,
   addr_1               VARCHAR(128)         NULL,
   addr_2               VARCHAR(128)         NULL,
   addr_3               VARCHAR(128)         NULL,
   city                 VARCHAR(64)          NULL,
   region               VARCHAR(32)          NULL,
   country              VARCHAR(2)              NULL,
   post_code            VARCHAR(16)          NULL,
   is_email_public      INTEGER                 NULL,
   is_acct_active       INTEGER                 NULL,
   security_question    INTEGER                 NULL,
   security_answer      VARCHAR(128)         NULL,
   date_created         TIMESTAMP            NULL,
   created_by           INTEGER                 NULL,
   last_updated         TIMESTAMP            NULL,
   updated_by           INTEGER                 NULL
);

-- ==============================================================
--  Table: user_permission
-- ==============================================================
CREATE TABLE user_permission
(
   user_permission_id INTEGER NOT NULL DEFAULT 0,
   usr_id INTEGER NOT NULL DEFAULT 0,
   permission_id INTEGER NOT NULL DEFAULT 0
);

-- ==============================================================
--  Alter prim Key
-- ==============================================================
ALTER TABLE login ADD PRIMARY KEY (login_id);
ALTER TABLE preference ADD PRIMARY KEY (preference_id);
ALTER TABLE organisation ADD PRIMARY KEY (organisation_id);
ALTER TABLE organisation_type ADD PRIMARY KEY (organisation_type_id);
ALTER TABLE permission ADD PRIMARY KEY (permission_id);
ALTER TABLE user_preference ADD PRIMARY KEY (user_preference_id);
ALTER TABLE role ADD PRIMARY KEY (role_id);
ALTER TABLE role_permission ADD PRIMARY KEY (role_permission_id);
ALTER TABLE org_preference ADD PRIMARY KEY (org_preference_id);
ALTER TABLE usr ADD PRIMARY KEY (usr_id);
ALTER TABLE user_permission ADD PRIMARY KEY (user_permission_id);

-- ==============================================================
--  Create Index
-- ==============================================================
CREATE INDEX ix_usr_id ON login (usr_id);
CREATE INDEX ix2_usr_id ON user_preference (usr_id);
CREATE INDEX ix3_usr_id ON user_permission (usr_id);
CREATE UNIQUE INDEX perm_name ON permission (name);
CREATE INDEX ix_permission_id ON role_permission (permission_id);
CREATE INDEX ix_role_id ON role_permission (role_id);
CREATE INDEX ix_preference_id ON user_preference (preference_id);
CREATE INDEX ix_organisation_id ON org_preference (organisation_id);
CREATE INDEX ix2_preference_id ON org_preference  (preference_id);
CREATE INDEX ix_permission_id ON user_permission (permission_id);

COMMIT;
