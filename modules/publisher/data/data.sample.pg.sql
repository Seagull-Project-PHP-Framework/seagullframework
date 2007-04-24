-- Last edited: Antonio J. Garcia 2007-04-24
-- Data dump for /publisher
-- leave subqueries on a single line in order that table prefixes works

BEGIN;


--
--  Dumping data for table item
--

INSERT INTO item VALUES (1,2,5,1,1,'2004-01-03 18:21:25','2004-03-16 22:38:38','2004-01-03 18:21:07','2009-01-03 00:00:00',4,0);

--
--  Dumping data for table item_addition
--

INSERT INTO item_addition VALUES (1, 1, 7, 'Content Reshuffle', 0);
INSERT INTO item_addition VALUES (2, 1, 8, '<p>Test out dynamic language switching here:</p>\r\n<table cellpadding=5 width="75%" align=center border=1>\r\n<tbody>\r\n<tr bgcolor=#cccccc>\r\n<td>\r\n<p align=center>&nbsp;<a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/de-iso-8859-1/"><img height=20 alt=germany.gif hspace=0 src="/seagull/www/themes/default/images/uploads/germany.gif" width=30 align=baseline border=0></a></p></td>\r\n<td>\r\n<p align=center>&nbsp;<a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh/"><img height=20 alt=china.gif hspace=0 src="/seagull/www/themes/default/images/uploads/china.gif" width=30 align=baseline border=0></a></p></td>\r\n<td>\r\n<p align=center>&nbsp;<a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh-tw/"><img style="WIDTH: 30px; HEIGHT: 20px" height=20 alt=taiwan.gif hspace=0 src="/seagull/www/themes/default/images/uploads/taiwan.gif" width=30 align=baseline border=0></a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/ru-win1251/"><img height=20 alt=russia.gif hspace=0 src="/seagull/www/themes/default/images/uploads/russia.gif" width=30 align=baseline border=0></a>&nbsp;</p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/en-iso-8859-1/"><img height=15 alt=uk.gif hspace=0 src="/seagull/www/themes/default/images/uploads/uk.gif" width=30 align=baseline border=0></a>&nbsp;</p></td></tr>\r\n<tr>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/de-iso-8859-1/">German</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh/">Chinese(GB2312)</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh-tw/">Chinese(Big5)</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/ru-win1251/">Russian</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/en-iso-8859-15/">English</a></p></td></tr></tbody></table>\r\n<p><strong>nb</strong>: to see the Chinese translation rendered properly english Windows users running Internet Explorer will need to install the Asian language pack - see <a href="http://marc.theaimsgroup.com/?l=seagull-general&amp;m=107814024805423&amp;w=2">this page</a> for more detail.</p>\r\n<p>Thanks to <a href="http://www.stargeek.com/">Dan''s</a> excellent <a href="http://www.stargeek.com/crawler_sim.php">web crawler simulation</a> tool that gives you a search engine view of your site, I''ve shuffled around PHPkitchen''s content in an attempt to put the more relevant stuff at the top.</p>\r\n<p>Please also checkout the new <strong>Thinking Outside of the Box</strong> block in the left column, this is a collection of links to some of the more interesting applications of PHP that have surfaced recently.</p>\r\n<p>Dan''s other tool, the <a href="http://www.stargeek.com/code_to_text.php">code to text analyser</a>, reveals PHPkitchen suffers from a high html bloat, this is being addressed in latest version of <a href="http://seagull.phpkitchen.com/">Seagull</a> project where John Dell is almost finished his XHTML theme.</p>', 0);

--
--  Dumping data for table category
--

INSERT INTO category VALUES (0,'MainRoot',NULL,0,0,0,0,0,0);
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


COMMIT;

