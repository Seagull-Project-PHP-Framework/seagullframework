/*==============================================================*/
/* Table: rate                                                  */
/*==============================================================*/
CREATE TABLE if NOT exists rate (
  rate_id int(11) NOT NULL default '0',
  date date NOT NULL default '0000-00-00',
  currency char(3) NOT NULL default '',
  rate int(12) NOT NULL default '0',
  last_updated datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (rate_id)
) ; 