-- ==============================================================
--  DBMS name:      IBM DB2                              
--  Created on:     2005-11-17 10:32:00


-- ==============================================================
--  Table: log_table                                             
-- ==============================================================
create table log_table (
id                   INTEGER                 not null,
logtime              TIMESTAMP            not null,
ident                CHAR(16)             not null,
priority             INTEGER                 not null,
message              VARCHAR(200)         ,
constraint PK_LOG_TABLE primary key (id)
);

-- ==============================================================
--  Table: table_lock                                            
-- ==============================================================
create table table_lock (
lockID               CHAR(32)             not null,
lockTable            CHAR(32)             not null,
lockStamp            INTEGER                 ,
constraint PK_TABLE_LOCK primary key (lockID, lockTable)
);

-- ==============================================================
--  Table: user_sessions
-- ==============================================================
CREATE TABLE user_session 
(
  session_id    varchar(255) NOT NULL,
  last_updated  TIMESTAMP       ,
  data_value    CLOB            ,
  usr_id        INTEGER 	NOT NULL,
  username      VARCHAR(64) DEFAULT NULL,
  expiry        INTEGER 	NOT NULL,
  constraint PK_SESSION primary key (session_id)
);

-- ==============================================================
--  Index: user_session_last_updated                        
-- ==============================================================

create  index user_sess_last_upd on user_session (
    last_updated
);

-- ==============================================================
--  Index: user_session_usr_id                        
-- ==============================================================

create  index user_sess_usr_id on user_session (
    usr_id
);
    
-- ==============================================================
--  Index: user_session_username                        
-- ==============================================================

create  index user_sess_username on user_session (
    username
);

-- ==============================================================
--  Function: unix_timestamp
-- ==============================================================
--CREATE FUNCTION unix_timestamp (datum IN VARCHAR2) RETURN NUMBER IS BEGIN RETURN ROUND((TO_DATE(datum) - TO_DATE('1970-01-01 00:00:00','YYYY-MM-DD HH24:MI:SS'))*86400,0); END;;


-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00

-- ==============================================================
--  Table: module                                                 
-- ==============================================================

create table module (
module_id         INTEGER not null,
is_configurable   SMALLINT ,
name              VARCHAR(255) ,
title             VARCHAR(255) ,
description       CLOB         ,
admin_uri         VARCHAR(255) ,
icon              VARCHAR(255) ,
constraint PK_MODULE primary key (module_id)
);
