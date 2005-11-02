# Generation Time: Nov 18, 2004 at 05:07 PM
# Server version: 3.23.58
# PHP Version: 5.0.2
# Database : `seagull`

#
# Dumping data for table `item`
#


INSERT INTO item VALUES (1,2,5,1,1,'2004-01-03 18:21:25','2004-03-16 22:38:38','2004-01-03 18:21:07','2009-01-03 00:00:00',4);

#
# Dumping data for table `item_addition`
#


INSERT INTO `item_addition` VALUES (1, 1, 7, 'Content Reshuffle');
INSERT INTO `item_addition` VALUES (2, 1, 8, '<p>Test out dynamic language switching here:</p>\r\n<table cellpadding=5 width="75%" align=center border=1>\r\n<tbody>\r\n<tr bgcolor=#cccccc>\r\n<td>\r\n<p align=center>&nbsp;<a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/de-iso-8859-1/"><img height=20 alt=germany.gif hspace=0 src="/seagull/www/themes/default/images/uploads/germany.gif" width=30 align=baseline border=0></a></p></td>\r\n<td>\r\n<p align=center>&nbsp;<a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh/"><img height=20 alt=china.gif hspace=0 src="/seagull/www/themes/default/images/uploads/china.gif" width=30 align=baseline border=0></a></p></td>\r\n<td>\r\n<p align=center>&nbsp;<a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh-tw/"><img style="WIDTH: 30px; HEIGHT: 20px" height=20 alt=taiwan.gif hspace=0 src="/seagull/www/themes/default/images/uploads/taiwan.gif" width=30 align=baseline border=0></a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/ru-win1251/"><img height=20 alt=russia.gif hspace=0 src="/seagull/www/themes/default/images/uploads/russia.gif" width=30 align=baseline border=0></a>&nbsp;</p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/en-iso-8859-1/"><img height=15 alt=uk.gif hspace=0 src="/seagull/www/themes/default/images/uploads/uk.gif" width=30 align=baseline border=0></a>&nbsp;</p></td></tr>\r\n<tr>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/de-iso-8859-1/">German</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh/">Chinese(GB2312)</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh-tw/">Chinese(Big5)</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/ru-win1251/">Russian</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/en-iso-8859-15/">English</a></p></td></tr></tbody></table>\r\n<p><strong>nb</strong>: to see the Chinese translation rendered properly english Windows users running Internet Explorer will need to install the Asian language pack - see <a href="http://marc.theaimsgroup.com/?l=seagull-general&amp;m=107814024805423&amp;w=2">this page</a> for more detail.</p>\r\n<p>Thanks to <a href="http://www.stargeek.com/">Dan''s</a> excellent <a href="http://www.stargeek.com/crawler_sim.php">web crawler simulation</a> tool that gives you a search engine view of your site, I''ve shuffled around PHPkitchen''s content in an attempt to put the more relevant stuff at the top.</p>\r\n<p>Please also checkout the new <strong>Thinking Outside of the Box</strong> block in the left column, this is a collection of links to some of the more interesting applications of PHP that have surfaced recently.</p>\r\n<p>Dan''s other tool, the <a href="http://www.stargeek.com/code_to_text.php">code to text analyser</a>, reveals PHPkitchen suffers from a high html bloat, this is being addressed in latest version of <a href="http://seagull.phpkitchen.com/">Seagull</a> project where John Dell is almost finished his XHTML theme.</p>');

#
# Dumping data for table `item_type`
#


INSERT INTO item_type VALUES (1,'All');
INSERT INTO item_type VALUES (2,'Html Article');
INSERT INTO item_type VALUES (4,'News Item');
INSERT INTO item_type VALUES (5,'Static Html Article');

#
# Dumping data for table `item_type_mapping`
#


INSERT INTO item_type_mapping VALUES (3,2,'title',0);
INSERT INTO item_type_mapping VALUES (4,2,'bodyHtml',2);
INSERT INTO item_type_mapping VALUES (5,4,'title',0);
INSERT INTO item_type_mapping VALUES (6,4,'newsHtml',2);
INSERT INTO item_type_mapping VALUES (7,5,'title',0);
INSERT INTO item_type_mapping VALUES (8,5,'bodyHtml',2);

#
# Dumping data for table `category`
#

INSERT INTO category VALUES (1,'PublisherRoot',NULL,0,1,1,4,1,1);
INSERT INTO category VALUES (2,'example',NULL,1,1,2,3,1,2);
INSERT INTO category VALUES (3,'OtherRoot',NULL,0,3,1,2,1,1);
INSERT INTO category VALUES (4,'Shop',NULL,0,4,1,16,2,1);
INSERT INTO category VALUES (6,'Printers',NULL,4,4,8,9,2,2);
INSERT INTO category VALUES (5,'Monitors',NULL,4,4,2,7,1,2);
INSERT INTO category VALUES (13,'CRT',NULL,5,4,3,4,1,3);
INSERT INTO category VALUES (7,'Laptop Computers',NULL,4,4,10,15,3,2);
INSERT INTO category VALUES (9,'Notebook',NULL,7,4,11,12,1,3);
INSERT INTO category VALUES (11,'Tablet PC',NULL,7,4,13,14,2,3);
INSERT INTO category VALUES (15,'LCD',NULL,5,4,5,6,2,3);


#
# Dumping data for table `module`
#
#INSERT INTO module VALUES (1, 1, 'block', 'Blocks', 'Use the ''Blocks'' module to configure the contents of the blocks in the left and right hand columns.', 'block/block', 'blocks.png');
#INSERT INTO module VALUES (2, 0, 'contactus', 'Contact Us', 'The ''Contact Us'' module can be used to present a form to your users allowing them to contact the site administrators.', NULL, 'contactus.png');
INSERT INTO module VALUES (3, 0, 'default', 'Default', 'The ''Default'' module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.', NULL, 'default.png');
#INSERT INTO module VALUES (4, 0, 'documentor', 'Documentor', '''Documentor'' is a module that lets you quickly and easily create documentation in html format based on articles you submit in the ''Publisher'' module.', NULL, 'documentor.png');
#INSERT INTO module VALUES (5, 1, 'faq', 'FAQs', 'Use the ''FAQ'' module to easily create a list of Frequently Asked Questions with corresponding answers for your site.', 'faq/faq', 'faqs.png');
#INSERT INTO module VALUES (6, 0, 'guestbook', 'Guestbook', 'Use the ''Guestbook'' to allow users to leave comments about your site.', 'guestbook/guestbook', 'core.png');
INSERT INTO module VALUES (7, 1, 'maintenance', 'Maintenance', 'The ''Maintenance'' module lets you take care of several application maintenance tasks, like cleaning up temporary files, managing interface language translations, rebuilding DataObjects files, etc.', 'maintenance/maintenance', 'maintenance.png');
#INSERT INTO module VALUES (8, 0, 'messaging', 'Messaging', 'The ''Messaging'' module contains classes for sending internal Instant Messages, managing external email sending, and managing your contacts.', NULL, 'messaging.png');
INSERT INTO module VALUES (9, 1, 'navigation', 'Navigation', 'The ''Navigation'' module is what you use to build your site navigation, it creates menus that you can customise in terms of look and feel, and allows you to link to any site resource.', 'navigation/page', 'navigation.png');
#INSERT INTO module VALUES (10, 1, 'newsletter', 'Newsletter', 'The ''Newsletter'' module is a simple mass mailer module that allows you to create an HTML formatted message or newsletter, and send it to all your registered users, or on a group by group basis, in a single click.', 'newsletter/list', 'newsletter.png');
#INSERT INTO module VALUES (11, 1, 'publisher', 'Publisher', 'The ''Publisher'' module allows you to create content and publish it to your site.  Currently you can create various types of articles and upload and categorise any filetype, matching the two together in a browsable archive format.', 'publisher/article', 'publisher.png');
INSERT INTO module VALUES (12, 1, 'user', 'Users and Security', 'The ''Users and Security'' module allows you to manage all your users, administer the roles they belong to, change their passwords, setup permissions and alter the global default preferences.', 'user/user', 'users.png');
#INSERT INTO module VALUES (13, 1, 'randommsg', 'Random Messages', 'Allows you to create a list of messages and display them randomly (fortune).', 'randommsg/rndmsg', 'rndmsg.png');
#INSERT INTO module VALUES (14, 0, 'export', 'Export Data', 'Used for exporting to various formats, ie RSS, OPML, etc.', 'export/rss', 'rndmsg.png');
#INSERT INTO module VALUES (15, 1, 'shop', 'Shop', 'This is the Shop Manager. Add and edit your products, prices and discounts here.', 'shop/shopadmin', 'default.png');
#INSERT INTO module VALUES (16, 1, 'rate', 'Currency', 'Here you can edit and update the currency rates.', 'rate/rateadmin', 'default.png');
#INSERT INTO module VALUES (17, 1, 'cart', 'Cart', 'Universal cart module with basket and order management.', 'cart/cartadmin', 'default.png');