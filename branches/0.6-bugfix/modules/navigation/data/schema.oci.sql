-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2004-12-15 10:32:00

-- ==============================================================
--  Table: section                                               
-- ==============================================================
create table section (
section_id           NUMBER(10)                 not null,
title                VARCHAR(32)                null,
resource_uri         VARCHAR(128)               null,
perms                VARCHAR(32)                null,
parent_id            NUMBER(10)                 null,
root_id              NUMBER(10)                 null,
left_id              NUMBER(10)                 null,
right_id             NUMBER(10)                 null,
order_id             NUMBER(10)                 null,
level_id             NUMBER(10)                 null,
is_enabled           NUMBER(5)                  null,
is_static            NUMBER(5)                  null,
access_key           CHAR(1)                    null,
rel                  VARCHAR(16)                null,
constraint PK_SECTION primary key (section_id)
);

-- ==============================================================
--  Index: root_id                                               
-- ==============================================================
create  index AK_section_root_id on section (
root_id
);

-- ==============================================================
--  Index: left_id                                               
-- ==============================================================
create  index AK_section_left_id on section (
left_id
);

-- ==============================================================
--  Index: ritgh_id                                              
-- ==============================================================
create  index AK_section_ritgh_id on section (
right_id
);

-- ==============================================================
--  Index: order_id                                              
-- ==============================================================
create  index AK_section_order_id on section (
order_id
);

-- ==============================================================
--  Index: level_id                                              
-- ==============================================================
create  index AK_section_level_id on section (
level_id
);

-- ==============================================================
--  Index: id_root_l_r                                           
-- ==============================================================
create  index AK_section_id_root_l_r on section (
section_id,
root_id,
left_id,
right_id
);
