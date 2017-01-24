-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Schema for /newsletter

BEGIN;


-- ==============================================================
-- Table: newsletter                                            
-- ==============================================================

create table newsletter (
  newsletter_id  int4          NOT NULL default '0',
  list           varchar(32)   NOT NULL default '',
  name           varchar(128)  NOT NULL default '',
  email          varchar(128)  NOT NULL default '',
  status         int           NOT NULL default '0',
  action_request varchar(32)   NOT NULL default '',
  action_key     varchar(64)   NOT NULL default '',
  date_created   timestamp     NOT NULL default '1970-01-01 00:00:00',
  last_updated   timestamp     NOT NULL default '1970-01-01 00:00:00',
  constraint pk_newsletter PRIMARY KEY  (newsletter_id)
) ; 

-- ==============================================================
--  Sequence: newsletter_seq
-- ==============================================================

create sequence newsletter_seq;

COMMIT;
