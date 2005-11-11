/*==============================================================*/
/* Table: document                                              */
/*==============================================================*/
create table if not exists document
(
   document_id                    int                            not null,
   category_id                    int,
   document_type_id               int                            not null,
   name                           varchar(128),
   file_size                      int,
   mime_type                      varchar(32),
   date_created                   datetime,
   added_by                       int,
   description                    text,
   num_times_downloaded           int,
   primary key (document_id)
);

/*==============================================================*/
/* Index: document_document_type_fk                             */
/*==============================================================*/
create index document_document_type_fk on document
(
   document_type_id
);

/*==============================================================*/
/* Index: category_document_fk                                  */
/*==============================================================*/
create index category_document_fk on document
(
   category_id
);

/*==============================================================*/
/* Table: document_type                                         */
/*==============================================================*/
create table if not exists document_type
(
   document_type_id               int                            not null,
   name                           varchar(32),
   primary key (document_type_id)
);
