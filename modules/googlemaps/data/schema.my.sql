/*==============================================================*/
/* Table: googlemaps_user_geocode                               */
/*==============================================================*/
create table if not exists googlemaps_user_geocode
(
   googlemaps_user_geocode_id     int                            not null,
   usr_id                         int,
   latitude                       float,
   longitude                      float,
   precision_estimate             varchar(16),
   last_updated                   datetime,
   primary key (googlemaps_user_geocode_id)
);
