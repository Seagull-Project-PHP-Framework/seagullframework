-- Last edited: Antonio J. Garcia 2007-04-21
-- Schema for default

-- ==============================================================
--  Table: log_table
-- ==============================================================
create table log_table
(
   id                   INT4                 not null,
   logtime              TIMESTAMP            not null,
   ident                CHAR(16)             not null,
   priority             INT4                 not null,
   message              VARCHAR(200)         null,
   constraint PK_LOG_TABLE primary key (id)
);

-- ==============================================================
--  Sequence: log_table_seq
-- ==============================================================

create sequence log_table_seq;

-- ==============================================================
--  Table: table_lock
-- ==============================================================
create table table_lock
(
   lockID               CHAR(32)             not null,
   lockTable            CHAR(32)             not null,
   lockStamp            INT4                 null,
   constraint PK_TABLE_LOCK primary key (lockID, lockTable)
);

-- ==============================================================
--  Sequence: table_lock_seq
-- ==============================================================

create sequence table_lock_seq;

-- ==============================================================
--  Table: session
-- ==============================================================
create table user_session (
   session_id                    VARCHAR(255)    not null,
   last_updated                  TIMESTAMP       null,
   data_value                    TEXT            null,
   usr_id                        INT4            not null default 0,
   username                      VARCHAR(64)     null,
   expiry                        INT4            not null default 0,
   constraint PK_SESSION primary key (session_id)
);

-- ==============================================================
--  Sequence: session_seq
-- ==============================================================

create sequence session_seq;

-- ==============================================================
--  Index: user_session_last_updated
-- ==============================================================

create index user_session_last_updated on user_session (
    last_updated
);

-- ==============================================================
--  Index: user_session_usr_id
-- ==============================================================

create index user_session_usr_id on user_session (
    usr_id
);

-- ==============================================================
--  Index: user_session_username
-- ==============================================================

create index user_session_username on user_session (
    username
);


-- ==============================================================
--  Table: module
-- ==============================================================

create table module (
   module_id         INT4 not null,
   is_configurable   INT2 null,
   name              VARCHAR(255) null,
   title             VARCHAR(255) null,
   description       TEXT         null,
   admin_uri         VARCHAR(255) null,
   icon              VARCHAR(255) null,
   maintainers       TEXT,
   version           VARCHAR(8)   null,
   license           VARCHAR(16)  null,
   state             VARCHAR(8)   null,
   constraint PK_MODULE primary key (module_id)
);

-- ==============================================================
-- sequence module_seq
-- ==============================================================

create sequence module_seq;

-- ==============================================================
--  Function: unix_timestamp
-- some functions for better compatibility with mysql master schema file
-- ==============================================================

CREATE OR REPLACE FUNCTION unix_timestamp(TIMESTAMP WITHOUT TIME ZONE) RETURNS BIGINT LANGUAGE SQL IMMUTABLE STRICT AS 'SELECT EXTRACT(EPOCH FROM $1)::bigint;';

CREATE OR REPLACE FUNCTION UNIX_TIMESTAMP(TIMESTAMP WITH TIME ZONE) RETURNS BIGINT LANGUAGE SQL IMMUTABLE STRICT AS 'SELECT EXTRACT(EPOCH FROM $1)::bigint;';

CREATE OR REPLACE FUNCTION FROM_UNIXTIME(BIGINT, VARCHAR) RETURNS TIMESTAMP WITH TIME ZONE LANGUAGE SQL IMMUTABLE STRICT AS 'SELECT TIMESTAMP WITH TIME ZONE \'epoch\' + $1 * interval \'1 second\';';

