-- Last edited: Pierpaolo Toniolo 26-07-2005
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
--  Table: session
-- ==============================================================
create table user_session 
(
   session_id                    VARCHAR(255)    not null,
   last_updated                  TIMESTAMP       null,
   data_value                    TEXT            null,
   usr_id                        INT4            not null,
   username                      VARCHAR(64)     null,
   expiry                        INT4            not null,   
   constraint PK_SESSION primary key (session_id)
);

-- ==============================================================
--  Index: user_session_last_updated                        
-- ==============================================================

create  index user_session_last_updated on user_session (
    last_updated
);

-- ==============================================================
--  Index: user_session_usr_id                        
-- ==============================================================

create  index user_session_usr_id on user_session (
    usr_id
);
    
-- ==============================================================
--  Index: user_session_username                        
-- ==============================================================

create  index user_session_username on user_session (
    username
);



-- ==============================================================
--  Function: unix_timestamp
-- ==============================================================
-- for this to work, you have to activate the language plpgsql by calling
-- "createlang plpgsql <dbname>" from commandline.

CREATE or replace FUNCTION unix_timestamp (timestamp)
RETURNS integer AS ' DECLARE datum ALIAS FOR $1; BEGIN RETURN EXTRACT (EPOCH FROM datum); END; ' LANGUAGE plpgsql;



-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Schema for /modules/default

BEGIN;

-- ==============================================================
--  Table: module                                                 
-- ==============================================================

create table module 
(
   module_id         INT4 not null,
   is_configurable   INT2 null,
   name              VARCHAR(255) null,
   title             VARCHAR(255) null,
   description       TEXT         null,
   admin_uri         VARCHAR(255) null,
   icon              VARCHAR(255) null,
   constraint PK_MODULE primary key (module_id)
);

COMMIT;
