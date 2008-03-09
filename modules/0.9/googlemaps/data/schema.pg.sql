-- Last edited: Antonio J. Garcia 2007-04-21

BEGIN;
/*==============================================================*/
/* Table: googlemaps_user_geocode                               */
/*==============================================================*/
create table googlemaps_user_geocode
(
   googlemaps_user_geocode_id     int                            not null,
   usr_id                         int,
   latitude                       float,
   longitude                      float,
   precision_estimate             varchar(16),
   last_updated                   timestamp,
   constraint PK_GOOGLEMAPS_USER_GEOCODE primary key (googlemaps_user_geocode_id)
);

create sequence googlemaps_user_geocode_seq;

COMMIT;
