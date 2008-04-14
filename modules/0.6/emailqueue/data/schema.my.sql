/*==============================================================*/
/* Table: email_queue                                           */
/*==============================================================*/
CREATE TABLE `email_queue` (
  `email_queue_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_to_send` datetime NOT NULL,
  `date_sent` datetime default NULL,
  `mail_headers` text NOT NULL,
  `mail_recipient` varchar(255) NOT NULL,
  `mail_body` longtext NOT NULL,
  `mail_subject` varchar(255) DEFAULT NULL,
  `attempts` smallint NOT NULL default 0,
  `usr_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,

  PRIMARY KEY(`email_queue_id`),
  KEY (`date_to_send`),
  KEY (`usr_id`)
);