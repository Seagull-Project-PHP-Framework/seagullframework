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
