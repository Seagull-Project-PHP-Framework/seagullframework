/*==============================================================*/
/* Table: guestbook                                             */
/*==============================================================*/
create table if not exists guestbook
(
   guestbook_id         int                                     not null,
   date_created         datetime,
   name                 varchar(255),
   email                varchar(255),
   message              text,
   primary key (guestbook_id)
);
