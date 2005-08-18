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

