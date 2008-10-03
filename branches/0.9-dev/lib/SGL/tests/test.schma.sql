/*==============================================================*/
/* Table: user_session                                          */
/*==============================================================*/
CREATE TABLE user_session (
  session_id varchar(255) NOT NULL,
  last_updated datetime,
  data_value text,
  usr_id int(11) NOT NULL,
  username varchar(64) default NULL,
  expiry int(11) NOT NULL,
  PRIMARY KEY  (session_id),
  KEY last_updated (last_updated),
  KEY usr_id (usr_id),
  KEY username (username)
);