/*==============================================================*/
/* Table: cart                                                  */
/*==============================================================*/
CREATE TABLE cart (
  cart_id int(11) NOT NULL default '0',
  usr_id int(11) NOT NULL default '0',
  total varchar(100) NOT NULL default '',
  total_sum varchar(100) NOT NULL default '0',
  total_sumVAT varchar(100) NOT NULL default '0',
  stage int(1) NOT NULL default '0',
  date_created datetime NOT NULL default '0000-00-00 00:00:00',
  status int(11) NOT NULL default '1',
  PRIMARY KEY  (cart_id)
);

/*==============================================================*/
/* Table: cart_product                                          */
/*==============================================================*/
CREATE TABLE cart_product (
  cart_product_id int(11) NOT NULL auto_increment,
  cart_id int(11) NOT NULL default '0',
  product_id int(10) unsigned NOT NULL default '0',
  product_name varchar(100) NOT NULL default '',
  product_code varchar(100) NOT NULL default '',
  quantity int(11) NOT NULL default '0',
  price varchar(100) NOT NULL default '0',
  priceVAT varchar(100) NOT NULL default '',
  PRIMARY KEY  (cart_product_id)
);

/*==============================================================*/
/* Table: payment                                               */
/*==============================================================*/
/*
CREATE TABLE payment (
  id int(11) NOT NULL auto_increment,
  user_id int(11) NOT NULL default '0',
  credit_limit decimal(16,2) unsigned NOT NULL default '0.00',
  debt decimal(16,2) NOT NULL default '0.00',
  debt_start_date datetime default NULL,
  last_payment datetime default NULL,
  payment_date_created datetime NOT NULL default '0000-00-00 00:00:00',
  payment_updated_by int(11) default NULL,
  last_updated datetime default NULL,
  PRIMARY KEY  (id)
);
*/
