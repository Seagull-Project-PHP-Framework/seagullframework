INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'publisher', 'Publisher', 'The ''Publisher'' module allows you to create content and publish it to your site.  Currently you can create various types of articles and upload and categorise any filetype, matching the two together in a browsable archive format.', 'publisher/article', 'publisher.png');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_add', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_insert', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_edit', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_update', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_delete', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_list', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'wikiscrapemgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'wikiscrapemgr_list', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articleviewmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_setDownload', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_view', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_download', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_downloadZipped', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_view', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articleviewmgr_view', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articleviewmgr_summary', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_changeStatus', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_view', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_reorder', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_reorderUpdate', NULL, @moduleId);

#guest role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'articleviewmgr_summary';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articleviewmgr_view';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'filemgr_download';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'filemgr_downloadZipped';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'filemgr_view';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'wikiscrapemgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_add';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_edit';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_insert';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_update';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_view';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articleviewmgr_summary';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articleviewmgr_view';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'filemgr_download';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'filemgr_downloadZipped';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'filemgr_view';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'wikiscrapemgr_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);



#
# Dumping data for table `document_type`
#

INSERT INTO document_type VALUES (1,'MS Word');
INSERT INTO document_type VALUES (2,'MS Excel');
INSERT INTO document_type VALUES (3,'MS Powerpoint');
INSERT INTO document_type VALUES (4,'URL');
INSERT INTO document_type VALUES (5,'Image');
INSERT INTO document_type VALUES (6,'PDF');
INSERT INTO document_type VALUES (7,'unknown');


#
# Dumping data for table `item`
#


#INSERT INTO item VALUES (1,2,5,1,1,'2004-01-03 18:21:25','2004-03-16 22:38:38','2004-01-03 18:21:07','2009-01-03 00:00:00',4);

#
# Dumping data for table `item_addition`
#


#INSERT INTO `item_addition` VALUES (1, 1, 7, 'Content Reshuffle');
#INSERT INTO `item_addition` VALUES (2, 1, 8, '<p>Test out dynamic language switching here:</p>\r\n<table cellpadding=5 width="75%" align=center border=1>\r\n<tbody>\r\n<tr bgcolor=#cccccc>\r\n<td>\r\n<p align=center>&nbsp;<a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/de-iso-8859-1/"><img height=20 alt=germany.gif hspace=0 src="/seagull/www/themes/default/images/uploads/germany.gif" width=30 align=baseline border=0></a></p></td>\r\n<td>\r\n<p align=center>&nbsp;<a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh/"><img height=20 alt=china.gif hspace=0 src="/seagull/www/themes/default/images/uploads/china.gif" width=30 align=baseline border=0></a></p></td>\r\n<td>\r\n<p align=center>&nbsp;<a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh-tw/"><img style="WIDTH: 30px; HEIGHT: 20px" height=20 alt=taiwan.gif hspace=0 src="/seagull/www/themes/default/images/uploads/taiwan.gif" width=30 align=baseline border=0></a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/ru-win1251/"><img height=20 alt=russia.gif hspace=0 src="/seagull/www/themes/default/images/uploads/russia.gif" width=30 align=baseline border=0></a>&nbsp;</p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/en-iso-8859-1/"><img height=15 alt=uk.gif hspace=0 src="/seagull/www/themes/default/images/uploads/uk.gif" width=30 align=baseline border=0></a>&nbsp;</p></td></tr>\r\n<tr>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/de-iso-8859-1/">German</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh/">Chinese(GB2312)</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/zh-tw/">Chinese(Big5)</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/ru-win1251/">Russian</a></p></td>\r\n<td>\r\n<p align=center><a href="/seagull/www/index.php/publisher/articleview/frmArticleID/1/staticId/6/lang/en-iso-8859-15/">English</a></p></td></tr></tbody></table>\r\n<p><strong>nb</strong>: to see the Chinese translation rendered properly english Windows users running Internet Explorer will need to install the Asian language pack - see <a href="http://marc.theaimsgroup.com/?l=seagull-general&amp;m=107814024805423&amp;w=2">this page</a> for more detail.</p>\r\n<p>Thanks to <a href="http://www.stargeek.com/">Dan''s</a> excellent <a href="http://www.stargeek.com/crawler_sim.php">web crawler simulation</a> tool that gives you a search engine view of your site, I''ve shuffled around PHPkitchen''s content in an attempt to put the more relevant stuff at the top.</p>\r\n<p>Please also checkout the new <strong>Thinking Outside of the Box</strong> block in the left column, this is a collection of links to some of the more interesting applications of PHP that have surfaced recently.</p>\r\n<p>Dan''s other tool, the <a href="http://www.stargeek.com/code_to_text.php">code to text analyser</a>, reveals PHPkitchen suffers from a high html bloat, this is being addressed in latest version of <a href="http://seagull.phpkitchen.com/">Seagull</a> project where John Dell is almost finished his XHTML theme.</p>');

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