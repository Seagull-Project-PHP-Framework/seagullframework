/*==============================================================*/
/* Table: cart                                                  */
/*==============================================================*/
CREATE TABLE if NOT exists cart (
  cart_id int(11) NOT NULL default '0',
  usr_id int(11) NOT NULL default '0',
  items text NOT NULL,
  items_count varchar(100) NOT NULL default '',
  total varchar(100) NOT NULL default '',
  stage int(1) NOT NULL default '0',
  date_created datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (cart_id)
) ;