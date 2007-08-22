-- Last edited: Antonio J. Garcia 2007-04-21
-- Schema for event

BEGIN;

-- ==============================================================
--  Table: event
-- ==============================================================

CREATE TABLE event (
  event_id              INT4                NOT NULL default 0,
  name                  VARCHAR(255)        NOT NULL default '',
  description           TEXT                NOT NULL,
  type_id               INT4                NOT NULL default 0,
  start_date            TIMESTAMP           NOT NULL default '1970-01-01 00:00:00',
  end_date              TIMESTAMP           NOT NULL default '1970-01-01 00:00:00',
  location_id           INT4                NOT NULL default 0,
  ticket_cost           VARCHAR(255)        NOT NULL default '',
  status                INT4                default NULL,
  date_created          TIMESTAMP           default NULL,
  last_updated          TIMESTAMP           default NULL,
  created_by            INT4                NOT NULL default 0,
  updated_by            INT4                NOT NULL default 0,
  constraint PK_EVENT primary key (event_id)
);

-- ==============================================================
--  Table: event
-- ==============================================================

CREATE TABLE event_type (
  event_type_id         INT4                NOT NULL default 0,
  name                  VARCHAR(64)         NOT NULL default '',
  constraint PK_EVENT_TYPE primary key (event_type_id)
);

-- ==============================================================
--  Table: event-media
-- ==============================================================

CREATE TABLE "event-media" (
  event_id              INT4                NOT NULL default 0,
  media_id              INT4                NOT NULL default 0,
  is_event_image        INT4                NOT NULL default 0
);

CREATE INDEX idx_event_media_1 ON "event-media" (
  event_id
);

CREATE INDEX idx_event_media_2 ON "event-media" (
  media_id
);

-- ==============================================================
--  Table: address
-- ==============================================================

CREATE TABLE address (
  address_id            INT4                NOT NULL default 0,
  addr_1                VARCHAR(128)        default NULL,
  addr_2                VARCHAR(128)        default NULL,
  addr_3                VARCHAR(128)        default NULL,
  city                  VARCHAR(64)         default NULL,
  region                VARCHAR(32)         default NULL,
  country               CHAR(2)             default NULL,
  post_code             VARCHAR(16)         default NULL,
  constraint PK_ADDRESS primary key (address_id)
);

-- ==============================================================
--  Table: location
-- ==============================================================

CREATE TABLE location (
  location_id           INT4                NOT NULL default 0,
  address_id            INT4                NOT NULL default 0,
  usr_id                INT4                NOT NULL default 0,
  name                  VARCHAR(255)        NOT NULL default '',
  email                 VARCHAR(255)        NOT NULL default '',
  telephone             VARCHAR(64)         NOT NULL default '',
  fax                   VARCHAR(64)         NOT NULL default '',
  website               VARCHAR(255)        NOT NULL default '',
  type_id               INT4                NOT NULL default 0,
  constraint PK_LOCATION primary key (location_id)
);

-- ==============================================================
--  Table: location_type
-- ==============================================================

CREATE TABLE location_type (
  location_type_id      INT4                NOT NULL default 0,
  name                  VARCHAR(64)         NOT NULL default '',
  constraint PK_LOCATION_TYPE primary key (location_type_id)
);

COMMIT;
