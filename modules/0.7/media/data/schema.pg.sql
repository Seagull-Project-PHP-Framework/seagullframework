-- Last edited: Pierpaolo Toniolo 15-08-2006
-- Schema for media

BEGIN;

-- ==============================================================
--  Table: media
-- ==============================================================

CREATE TABLE media (
  media_id              INT4                NOT NULL default 0,
  file_type_id          INT4                NOT NULL default 0,
  name                  VARCHAR(128)        default NULL,
  file_name             VARCHAR(255)        NOT NULL default '',
  file_size             INT4                default NULL,
  mime_type             VARCHAR(32)         default NULL,
  date_created          TIMESTAMP           default NULL,
  added_by              INT4                default NULL,
  description           TEXT,
  num_times_downloaded  INT4                default NULL,
  constraint pk_media primary key (media_id)
);

-- ==============================================================
--  Table: file_type
-- ==============================================================

create table file_type
(
   file_type_id         INT4                not null,
   name                 VARCHAR(32),
   constraint pk_file_type primary key (file_type_id)
);

COMMIT;

