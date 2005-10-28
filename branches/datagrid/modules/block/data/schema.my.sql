/*==============================================================*/
/* Table: block                                                 */
/*==============================================================*/
create table if not exists block
(
   block_id                       int                            not null,
   name                           varchar(64),
   title                          varchar(32),
   title_class                    varchar(32),
   body_class                     varchar(32),
   blk_order                      smallint,
   is_onleft                      smallint,
   is_enabled                     smallint,
   content longtext,
   primary key (block_id)
);

/*==============================================================*/
/* Table: block_assignment                                      */
/*==============================================================*/
create table if not exists block_assignment
(
   block_id                       int                            not null,
   section_id                     int                            not null,
   primary key (block_id, section_id)
);

/*==============================================================*/
/* Index: block_assignment_fk                                   */
/*==============================================================*/
create index block_assignment_fk on block_assignment
(
   block_id
);

/*==============================================================*/
/* Index: block_assignment_fk2                                  */
/*==============================================================*/
create index block_assignment_fk2 on block_assignment
(
   section_id
);

-- ==============================================================
--  table block_roles
--  DK 
-- ==============================================================
create table if not exists block_roles (
    block_id integer not null,
    role_id integer not null
);
