/*==============================================================*/
/* Table: newsletter                                            */
/*==============================================================*/
CREATE TABLE if NOT exists newsletter (
  newsletter_id int(11) NOT NULL default '0',
  list varchar(32) NOT NULL default '',
  name varchar(128) NOT NULL default '',
  email varchar(128) NOT NULL default '',
  status int(1) NOT NULL default '0',
  action_request varchar(32) NOT NULL default '',
  action_key varchar(64) NOT NULL default '',
  date_created datetime NOT NULL default '0000-00-00 00:00:00',
  last_updated datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (newsletter_id)
) ; 
