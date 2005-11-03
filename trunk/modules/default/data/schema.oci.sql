-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00
--  Changes:        session -> sessions                  


-- ==============================================================
--  Table: log_table                                             
-- ==============================================================
create table log_table (
id                   NUMBER(10)                 not null,
logtime              DATE            not null,
ident                CHAR(16)             not null,
priority             NUMBER(10)                 not null,
message              VARCHAR(200)         null,
constraint PK_LOG_TABLE primary key (id)
);

-- ==============================================================
--  Table: table_lock                                            
-- ==============================================================
create table table_lock (
lockID               CHAR(32)             not null,
lockTable            CHAR(32)             not null,
lockStamp            NUMBER(10)                 null,
constraint PK_TABLE_LOCK primary key (lockID, lockTable)
);

-- ==============================================================
--  Table: user_sessions
-- ==============================================================
CREATE TABLE user_session 
(
  session_id    varchar(255) NOT NULL,
  last_updated  DATE       null,
  data_value    CLOB            null,
  usr_id        NUMBER(10) 	NOT NULL,
  username      VARCHAR(64) DEFAULT NULL,
  expiry        NUMBER(10) 	NOT NULL,
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
CREATE FUNCTION unix_timestamp (datum IN VARCHAR2) RETURN NUMBER IS BEGIN RETURN ROUND((TO_DATE(datum) - TO_DATE('1970-01-01 00:00:00','YYYY-MM-DD HH24:MI:SS'))*86400,0); END;;


-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00

-- ==============================================================
--  Table: module                                                 
-- ==============================================================

create table module (
module_id         NUMBER(10) not null,
is_configurable   NUMBER(5) null,
name              VARCHAR(255) null,
title             VARCHAR(255) null,
description       CLOB         null,
admin_uri         VARCHAR(255) null,
icon              VARCHAR(255) null,
constraint PK_MODULE primary key (module_id)
);
