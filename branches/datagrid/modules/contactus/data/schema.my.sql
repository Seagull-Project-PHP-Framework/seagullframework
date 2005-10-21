/*==============================================================*/
/* Table: contact_us                                            */
/*==============================================================*/
create table if not exists contact_us
(
   contact_us_id                  int                            not null,
   first_name                     varchar(64),
   last_name                      varchar(32),
   email                          varchar(128),
   enquiry_type                   varchar(32),
   user_comment                   text,
   primary key (contact_us_id)
);
