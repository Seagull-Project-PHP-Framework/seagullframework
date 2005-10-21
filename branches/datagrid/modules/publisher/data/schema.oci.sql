-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00
--  Changes:        document.size -> document.docsize    

-- ==============================================================
--  Table: document                                              
-- ==============================================================
create table document (
document_id          NUMBER(10)                 not null,
category_id          NUMBER(10)                 null,
document_type_id     NUMBER(10)                 not null,
name                 VARCHAR(128)         null,
file_size            NUMBER(10)                 null,
mime_type            VARCHAR(32)          null,
date_created         DATE            null,
added_by             NUMBER(10)                 null,
description          CLOB                 null,
num_times_downloaded NUMBER(10)                 null,
constraint PK_DOCUMENT primary key (document_id)
);

-- ==============================================================
--  Index: document_document_type_fk                             
-- ==============================================================
create  index document_document_type_fk on document (
document_type_id
);

-- ==============================================================
--  Index: category_document_fk                                  
-- ==============================================================
create  index category_document_fk on document (
category_id
);

-- ==============================================================
--  Table: document_type                                         
-- ==============================================================
create table document_type (
document_type_id     NUMBER(10)                 not null,
name                 VARCHAR(32)          null,
constraint PK_DOCUMENT_TYPE primary key (document_type_id)
);
