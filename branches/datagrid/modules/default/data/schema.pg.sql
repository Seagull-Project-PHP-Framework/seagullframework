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
