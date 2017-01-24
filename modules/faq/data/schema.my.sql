/*==============================================================*/
/* Table: faq                                                   */
/*==============================================================*/
create table if not exists faq
(
   faq_id                         int                            not null,
   date_created                   datetime,
   last_updated                   datetime,
   question                       varchar(255),
   answer                         text,
   item_order                     int,
   primary key (faq_id)
);
