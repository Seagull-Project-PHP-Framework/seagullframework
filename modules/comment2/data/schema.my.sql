CREATE TABLE IF NOT EXISTS `comment` (
    `comment_id` int(10) unsigned NOT NULL,
    `comment_fk` int(11) default NULL,
    `full_name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `url` varchar(255) default NULL,
    `ip` varchar(16) default NULL,
    `user_agent` varchar(255) default NULL,
    `referrer` varchar(255) default NULL,
    `is_subscribed` smallint(6) default '0',
    `status_id` smallint(6) NOT NULL default '0',
    `body` text NOT NULL,
    `date_created` datetime default NULL,
    `created_by` int(11) default NULL,
    `last_updated` datetime default NULL,
    `updated_by` int(11) default NULL,

  PRIMARY KEY  (`comment_id`),
  KEY status_id (`status_id`)
);
