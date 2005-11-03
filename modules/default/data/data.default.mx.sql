-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL
-- ==========================================================================

--
--  Dumping data for table `module`
--

INSERT INTO module VALUES (1,1,'block','Blocks','Use the "Blocks" module to configure the contents of the blocks in the left and right hand columns.','block/block','blocks.png');
INSERT INTO module VALUES (2,0,'contactus','Contact Us','The "Contact Us" module can be used to present a form to your users allowing them to contact the site administrators.',NULL,'contactus.png');
INSERT INTO module VALUES (3,0,'default','Default','The "Default" module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.',NULL,'default.png');
INSERT INTO module VALUES (4,0,'documentor','Documentor','"Documentor" is a module that lets you quickly and easily create documentation in html format based on articles you submit in the "Publisher" module.',NULL,'documentor.png');
INSERT INTO module VALUES (5,1,'faq','FAQs','Use the "FAQ" module to easily create a list of Frequently Asked Questions with corresponding answers for your site.','faq/faq','faqs.png');
INSERT INTO module VALUES (6,0,'guestbook','Guestbook','Use the "Guestbook" to allow users to leave comments about your site.','guestbook/guestbook','core.png');
INSERT INTO module VALUES (7,1,'maintenance','Maintenance','The "Maintenance" module lets you take care of several application maintenance tasks, like cleaning up temporary files, managing interface language translations, rebuilding DataObjects files, etc.','maintenance/maintenance','maintenance.png');
INSERT INTO module VALUES (8,0,'messaging','Messaging','The "Messaging" module contains classes for sending internal Instant Messages, managing external email sending, and managing your contacts.',NULL,'messaging.png');
INSERT INTO module VALUES (9,1,'navigation','Navigation','The "Navigation" module is what you use to build your site navigation, it creates menus that you can customise in terms of look and feel, and allows you to link to any site resource.','navigation/page','navigation.png');
INSERT INTO module VALUES (10,1,'newsletter','Newsletter','The "Newsletter" module is a simple mass mailer module that allows you to create an HTML formatted message or newsletter, and send it to all your registered users, or on a group by group basis, in a single click.','newsletter/list','newsletter.png');
INSERT INTO module VALUES (11,1,'publisher','Publisher','The "Publisher" module allows you to create content and publish it to your site.  Currently you can create various types of articles and upload and categorise any filetype, matching the two together in a browsable archive format.','publisher/article','publisher.png');
INSERT INTO module VALUES (12,1,'user','Users and Security','The "Users and Security" module allows you to manage all your users, administer the roles they belong to, change their passwords, setup permissions and alter the global default preferences.','user/user','users.png');
INSERT INTO module VALUES (13,1,'randommsg','Random Messages','Allows you to create a list of messages and display them randomly (fortune).','randommsg/rndmsg','rndmsg.png');
INSERT INTO module VALUES (14, 0, 'export', 'Export Data', 'Used for exporting to various formats, ie RSS, OPML, etc.', 'export/rss', 'rndmsg.png');

COMMIT;
