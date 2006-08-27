CREATE TABLE `event` (
  `event_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `type_id` int(11) NOT NULL default '0',
  `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `location_id` int(11) NOT NULL default '0',
  `ticket_cost` varchar(255) NOT NULL default '',
  `status` smallint(6) default NULL,
  `date_created` datetime default NULL,
  `last_updated` datetime default NULL,
  `created_by` int(11) NOT NULL default '0',
  `updated_by` int(11) NOT NULL default '0',
  PRIMARY KEY  (`event_id`)
) TYPE=MyISAM;

CREATE TABLE `event_type` (
  `event_type_id` int(11) NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`event_type_id`)
) TYPE=MyISAM;

CREATE TABLE `event-media` (
  `event_id` int(11) NOT NULL default '0',
  `media_id` int(11) NOT NULL default '0',
  `is_event_image` smallint(6) NOT NULL default '0',
  KEY   (`event_id`),
  KEY   (`media_id`)
) TYPE=MyISAM;

CREATE TABLE `address` (
  `address_id` int(11) NOT NULL default '0',
  `addr_1` varchar(128) default NULL,
  `addr_2` varchar(128) default NULL,
  `addr_3` varchar(128) default NULL,
  `city` varchar(64) default NULL,
  `region` varchar(32) default NULL,
  `country` char(2) default NULL,
  `post_code` varchar(16) default NULL,
  PRIMARY KEY  (`address_id`)
) TYPE=MyISAM;

CREATE TABLE `location` (
  `location_id` int(11) NOT NULL default '0',
  `address_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `telephone` varchar(64) NOT NULL default '',
  `fax` varchar(64) NOT NULL default '',
  `website` varchar(255) NOT NULL default '',
  `type_id` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`location_id`)
) TYPE=MyISAM;


CREATE TABLE `location_type` (
  `location_type_id` int(11) NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`location_type_id`)
) TYPE=MyISAM;