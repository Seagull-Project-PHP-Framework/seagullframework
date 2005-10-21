-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Schema for /modules/publisher

BEGIN;

-- ==============================================================
--  Table: document                                              
-- ==============================================================
create table document 
(
   document_id          INT4                 not null,
   category_id          INT4                 null,
   document_type_id     INT4                 not null,
   name                 VARCHAR(128)         null,
   file_size            INT4                 null,
   mime_type            VARCHAR(32)          null,
   date_created         TIMESTAMP            null,
   added_by             INT4                 null,
   description          TEXT                 null,
   num_times_downloaded INT4                 null,
   constraint PK_DOCUMENT primary key (document_id)
);

-- ==============================================================
--  Index: document_document_type_fk                             
-- ==============================================================
create  index document_document_type_fk on document 
(
   document_type_id
);

-- ==============================================================
--  Index: category_document_fk                                  
-- ==============================================================
create  index category_document_fk on document 
(
   category_id
);

-- ==============================================================
--  Table: document_type                                         
-- ==============================================================
create table document_type 
(
   document_type_id     INT4                 not null,
   name                 VARCHAR(32)          null,
   constraint PK_DOCUMENT_TYPE primary key (document_type_id)
);

COMMIT;
