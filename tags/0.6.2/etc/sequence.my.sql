/*==============================================================*/
/* Table: sequence                                              */
/*==============================================================*/
create table if not exists sequence
(
   name                           varchar(64)                    not null,
   id                             bigint,
   primary key (name)
);
