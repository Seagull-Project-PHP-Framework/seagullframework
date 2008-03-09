CREATE TABLE `media` (
  `media_id` int(11) NOT NULL default '0',
  `file_type_id` int(11) NOT NULL default '0',
  `name` varchar(128) default NULL,
  `file_name` varchar(255) NOT NULL default '',
  `file_size` int(11) default NULL,
  `mime_type` varchar(32) default NULL,
  `date_created` datetime default NULL,
  `added_by` int(11) default NULL,
  `description` text,
  `num_times_downloaded` int(11) default NULL,
  PRIMARY KEY  (`media_id`)
);

create table if not exists file_type
(
   file_type_id               int                            not null,
   name                       varchar(32),
   primary key (file_type_id)
);
