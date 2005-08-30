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
perms                VARCHAR(16)                null,
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

-- ==============================================================
-- Table: category                                       
-- ==============================================================
CREATE TABLE category (
  category_id NUMBER(10) 	NOT NULL,
  label VARCHAR(32) 		DEFAULT NULL,
  perms VARCHAR(32) 		DEFAULT NULL,
  parent_id NUMBER(10) 		DEFAULT NULL,
  root_id NUMBER(10) 		DEFAULT NULL,
  left_id NUMBER(10) 		DEFAULT NULL,
  right_id NUMBER(10) 		DEFAULT NULL,
  order_id NUMBER(10) 		DEFAULT NULL,
  level_id NUMBER(10) 		DEFAULT NULL,
  CONSTRAINT PK_CATEGORY PRIMARY KEY (category_id)
);

-- ==============================================================
--  Index: root_id                                               
-- ==============================================================
create  index AK_category_root_id on category (
root_id
);

-- ==============================================================
--  Index: left_id                                               
-- ==============================================================
create  index AK_category_left_id on category (
left_id
);

-- ==============================================================
--  Index: ritgh_id                                              
-- ==============================================================
create  index AK_category_right_id on category (
right_id
);

-- ==============================================================
--  Index: order_id                                              
-- ==============================================================
create  index AK_category_order_id on category (
order_id
);

-- ==============================================================
--  Index: level_id                                              
-- ==============================================================
create  index AK_category_level_id on category (
level_id
);

-- ==============================================================
--  Index: id_root_l_r                                           
-- ==============================================================
create  index AK_category_id_root_l_r on category (
category_id,
root_id,
left_id,
right_id
);
