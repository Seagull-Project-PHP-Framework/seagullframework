/*==============================================================*/
/* Table: rndmsg_message                                        */
/*==============================================================*/
create table if not exists rndmsg_message (
  rndmsg_message_id             int                             not null,
  msg                           text,
  primary key  (rndmsg_message_id)
);
