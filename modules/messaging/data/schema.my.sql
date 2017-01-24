/*==============================================================*/
/* Table: contact                                               */
/*==============================================================*/
create table if not exists contact
(
   contact_id                     int                            not null,
   usr_id                         int                            not null,
   originator_id                  int,
   date_created                   datetime                       not null,
   primary key (contact_id)
);

/*==============================================================*/
/* Index: usr_contact_fk                                        */
/*==============================================================*/
create index usr_contact_fk on contact
(
   usr_id
);
/*==============================================================*/
/* Table: instant_message                                       */
/*==============================================================*/
create table if not exists instant_message
(
   instant_message_id             int                            not null,
   user_id_from                   int                            not null,
   user_id_to                     int                            not null,
   msg_time                       datetime,
   subject                        varchar(128),
   body                           text,
   delete_status                  smallint,
   read_status                    smallint,
   primary key (instant_message_id)
);

/*==============================================================*/
/* Index: usr_instant_from_fk                                   */
/*==============================================================*/
create index usr_instant_from_fk on instant_message
(
   user_id_to
);

/*==============================================================*/
/* Index: ust_instant_to_fk                                     */
/*==============================================================*/
create index ust_instant_to_fk on instant_message
(
   user_id_from
);
