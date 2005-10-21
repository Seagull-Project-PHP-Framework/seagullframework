#
# Insert into module-manager (use existing faqs icon)
# 
# !!!! you probably need to edit the module_id value !!!!
#

INSERT INTO `module` ( `module_id` , `is_configurable` , `name` , `title` , `description` , `admin_uri` , `icon` ) 
VALUES (
'13', '1', 'rndmsg', 'Random Messages', 'Manage the available random messages and groups.', 'rndmsgMgr.php', 'faqs.png'
);


#
# Table structure for table `rndmsg_message`
#

CREATE TABLE `rndmsg_message` (
  `rndmsg_message_id` int(11) NOT NULL default '0',
  `msg` text NOT NULL,
  PRIMARY KEY  (`rndmsg_message_id`)
) TYPE=MyISAM;
