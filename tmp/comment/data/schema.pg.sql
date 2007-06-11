-- Last edited: Antonio J. Garcia 2007-04-21
BEGIN;
CREATE TABLE comment (
  comment_id integer NOT NULL,
  entity_name varchar(16) NOT NULL,
  entity_id integer default NULL,
  full_name varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  url varchar(255) default NULL,
  ip varchar(16) default NULL,
  user_agent varchar(255) default NULL,
  referrer varchar(255) default NULL,
  type varchar(16) NOT NULL,
  is_subscribed smallint default '0',
  status_id smallint NOT NULL default '0',
  body text NOT NULL,
  date_created TIMESTAMP NOT NULL,
  constraint PK_COMMENT primary key (comment_id)
);
create sequence comment_seq;
create index status_idx on comment
(
   status_id
);
COMMIT;
