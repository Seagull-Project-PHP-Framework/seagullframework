-- ==============================================================
--  DBMS name:      IBM DB2                              
--  Created on:     2005-11-17 10:32:00

-- ==============================================================
--  Table: section                                               
-- ==============================================================
create table section (
section_id           INTEGER                 not null,
title                VARCHAR(32)                ,
resource_uri         VARCHAR(128)               ,
perms                VARCHAR(32)                ,
parent_id            INTEGER                 ,
root_id              INTEGER                 ,
left_id              INTEGER                 ,
right_id             INTEGER                 ,
order_id             INTEGER                 ,
level_id             INTEGER                 ,
is_enabled           SMALLINT                  ,
is_static            SMALLINT                  ,
access_key           CHAR(1)                    ,
rel                  VARCHAR(16)                ,
constraint PK_SECTION primary key (section_id)
);

-- ==============================================================
--  Index: root_id                                               
-- ==============================================================
create  index AK_sect_root_id on section (
root_id
);

-- ==============================================================
--  Index: left_id                                               
-- ==============================================================
create  index AK_sect_left_id on section (
left_id
);

-- ==============================================================
--  Index: ritgh_id                                              
-- ==============================================================
create  index AK_sect_right_id on section (
right_id
);

-- ==============================================================
--  Index: order_id                                              
-- ==============================================================
create  index AK_sect_order_id on section (
order_id
);

-- ==============================================================
--  Index: level_id                                              
-- ==============================================================
create  index AK_sect_level_id on section (
level_id
);

-- ==============================================================
--  Index: id_root_l_r                                           
-- ==============================================================
create  index AK_sect_id_root_lr on section (
section_id,
root_id,
left_id,
right_id
);
