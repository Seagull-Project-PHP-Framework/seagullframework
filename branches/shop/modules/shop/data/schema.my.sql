/*==============================================================*/
/* Table: product                                               */
/*==============================================================*/
CREATE TABLE if NOT exists product ( 
  product_id int(10) unsigned NOT NULL default '0', 
  cod1 varchar(100) NOT NULL default '', 
  cod2 varchar(100) NOT NULL default '', 
  name varchar(100) NOT NULL default '', 
  variantID int(10) unsigned default '0', 
  short_description text NOT NULL, 
  description text NOT NULL, 
  order_id int(10) unsigned NOT NULL default '0', 
  link_datasheet varchar(100) NOT NULL default '', 
  img varchar(100) NOT NULL default '', 
  manufacturer varchar(100) NOT NULL default '', 
  link_manufacturer varchar(100) NOT NULL default '', 
  price decimal(16,2) unsigned NOT NULL default '0.00', 
  currency char(3) NOT NULL default '', 
  warranty varchar(100) NOT NULL default '', 
  cat_id varchar(100) NOT NULL default '', 
  promotion varchar(100) NOT NULL default '', 
  new_id smallint(6) NOT NULL default '0', 
  bargain smallint(6) NOT NULL default '0', 
  status varchar(100) NOT NULL default '', 
  date_created datetime default '0000-00-00 00:00:00', 
  created_by int(11) default '0', 
  last_updated datetime default '0000-00-00 00:00:00', 
  updated_by int(11) default '0', 
  BALANCE varchar(11) NOT NULL default '', 
  PRIMARY KEY  (product_id) 
);

/*==============================================================*/
/* Table: price                                        */
/*==============================================================*/
CREATE TABLE if NOT exists price (
  price_id int(10) unsigned NOT NULL default '0',
  product_id int(10) unsigned NOT NULL default '0',
  usr_id int(11) NOT NULL default '0',
  price decimal(16,2) unsigned NOT NULL default '0.00',
  currency char(3) NOT NULL default '',
  date_created datetime default '0000-00-00 00:00:00',
  created_by int(11) default '0',
  last_updated datetime default '0000-00-00 00:00:00',
  updated_by int(11) default '0',
  PRIMARY KEY  (price_id)
); 

/*==============================================================*/
/* Table: category_description                                        */
/*==============================================================*/
CREATE TABLE category_description (
  ID int(11) NOT NULL auto_increment,
  cat_id int(11) NOT NULL default '0',
  description text,
  PRIMARY KEY  (ID)
);