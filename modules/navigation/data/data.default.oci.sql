--
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'navigation', 'Navigation', 'The ''Navigation'' module is what you use to build your site navigation, it creates menus that you can customise in terms of look and feel, and allows you to link to any site resource.', 'navigation/page', 'navigation.png', '', NULL, NULL, NULL);

--
-- Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr_cmd_changeStyle', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr_cmd_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_reorder', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_list', '', (SELECT MAX(module_id) FROM module));

--
-- Dumping data for table section
--

--INSERT INTO section VALUES (0, 'none', '', '0', 0, 0, 1, 2, 0, 1, 0, 0, 0, 0);
--INSERT INTO section VALUES (1, 'Home', '', '1,2,0', 0, 1, 1, 2, 1, 1, 1, 0, 1, 0);
--INSERT INTO section VALUES (2, 'Articles', 'publisher/articleview', '1,2,0', 0, 2, 1, 2, 6, 1, 1, 0, 0, 0);
--INSERT INTO section VALUES (3, 'FAQ', 'faq/faq', '1,2,0', 0, 3, 1, 2, 7, 1, 1, 0, 0, 0);
--INSERT INTO section VALUES (4, 'My Account', 'user/account', '1,2', 0, 4, 1, 2, 9, 1, 1, 0, 0, 0);
--INSERT INTO section VALUES (5, 'Messages', 'messaging/imessage', '1,2', 0, 5, 1, 2, 5, 1, 1, 0, 0, 0);
--INSERT INTO section VALUES (6, 'Sample', 'publisher/articleview/frmArticleID/1', '0', 0, 6, 1, 2, 4, 1, 1, 1, 0, 0);
--INSERT INTO section VALUES (7, 'Contact Us', 'contactus/contactus', '1,2,0', 0, 7, 1, 8, 2, 1, 1, 0, 9, 0);
--INSERT INTO section VALUES (8, 'Register Now', 'user/register', '0', 0, 8, 1, 2, 8, 1, 1, 0, 0, 0);
--INSERT INTO section VALUES (9, 'Publisher', 'publisher/article', '2', 0, 9, 1, 2, 3, 1, 1, 0, 0, 0);
--INSERT INTO section VALUES (11, 'testSection', 'faq/faq', '0', 15, 7, 5, 6, 1, 3, 1, 0, 0, 0);
--INSERT INTO section VALUES (12, 'Modules', 'default/module', '1', 0, 12, 1, 4, 11, 1, 1, 0, 0, 0);
--INSERT INTO section VALUES (13, 'Configuration', 'default/config', '1', 0, 13, 1, 2, 10, 1, 1, 0, 0, 0);
--INSERT INTO section VALUES (14, 'Get a quote', 'contactus/contactus/action/list/enquiry_type/Get a quote', '1,2,0', 7, 7, 2, 3, 1, 2, 1, 0, 0, 0);
--INSERT INTO section VALUES (15, 'Hosting info', 'contactus/contactus/action/list/enquiry_type/Hosting info', '1,2,0', 7, 7, 4, 7, 2, 2, 1, 0, 0, 0);
--INSERT INTO section VALUES (16, 'Manage', 'default/module/action/list', '1', 12, 12, 2, 3, 1, 2, 1, 0, 0, 0);
--INSERT INTO section VALUES (17, 'PubCategories', 'navigation/category', '1', 0, 17, 1, 2, 12, 1, 0, 0, 0, 0);
--INSERT INTO section VALUES (18, 'PubDocuments', 'publisher/document', '1', 0, 18, 1, 2, 13, 1, 0, 0, 0, 0);
--INSERT INTO section VALUES (19, 'PubArticles', 'publisher/article', '1', 0, 19, 1, 2, 14, 1, 0, 0, 0, 0);
--INSERT INTO section VALUES (22, 'Shop', 'shop/priceadmin', '1,2,0', 0, 22, 1, 4, 15, 1, 0, 0, 0, 0);
--INSERT INTO section VALUES (24, 'ShopAdmin', 'shop/shopadmin', '1', 22, 22, 2, 3, 1, 2, 0, 0, 0, 0);
--INSERT INTO section VALUES (25, 'Price', 'shop/price', '1,2,0', 0, 25, 1, 4, 16, 1, 0, 0, 0, 0);

--
-- Creating sequences
-- sequence must start on the first free record id
--

--CREATE SEQUENCE section_seq START WITH 26;


INSERT INTO section VALUES (0, 'root', 'uriEmpty:', '1', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO section VALUES (2, 'User menu', 'uriEmpty:', '-2', 2, 0, 2, 1, 6, 1, 1, 1, 0, '', '');
INSERT INTO section VALUES (4, 'Admin menu', 'uriEmpty:', '1', 4, 0, 4, 1, 42, 2, 1, 1, 0, '', '');
INSERT INTO section VALUES (9, 'My Account', 'user/account', '2', 9, 2, 2, 4, 5, 2, 2, 1, 0, '', '');
INSERT INTO section VALUES (6, 'Home', 'default/default', '-2', 6, 2, 2, 2, 3, 1, 2, 1, 0, '', '');
INSERT INTO section VALUES (51, 'Manage navigation', 'navigation/section', '1', 51, 23, 4, 17, 18, 1, 3, 1, 0, '', '');
INSERT INTO section VALUES (13, 'General', 'default/module', '1', 13, 4, 4, 2, 15, 1, 2, 1, 0, '', '');
INSERT INTO section VALUES (15, 'Configuration', 'default/config', '1', 15, 13, 4, 5, 6, 2, 3, 1, 0, '', '');
INSERT INTO section VALUES (17, 'Maintenance', 'default/maintenance', '1', 17, 13, 4, 7, 8, 3, 3, 1, 0, '', '');
INSERT INTO section VALUES (19, 'Module Generator', 'default/modulegeneration', '1', 19, 13, 4, 9, 10, 4, 3, 1, 0, '', '');
INSERT INTO section VALUES (21, 'Translation', 'default/translation', '1', 21, 13, 4, 11, 12, 5, 3, 1, 0, '', '');
INSERT INTO section VALUES (23, 'Navigation', 'navigation/section', '1', 23, 4, 4, 16, 19, 2, 2, 1, 0, '', '');
INSERT INTO section VALUES (25, 'Blocks', 'block/block', '1', 25, 4, 4, 20, 23, 3, 2, 1, 0, '', '');
INSERT INTO section VALUES (35, 'Users and security', 'user/user', '1', 35, 4, 4, 24, 33, 4, 2, 1, 0, '', '');
INSERT INTO section VALUES (37, 'Manage users', 'user/user', '1', 37, 35, 4, 25, 26, 1, 3, 1, 0, '', '');
INSERT INTO section VALUES (39, 'Manage permissions', 'user/permission', '1', 39, 35, 4, 27, 28, 2, 3, 1, 0, '', '');
INSERT INTO section VALUES (41, 'Manage roles', 'user/role', '1', 41, 35, 4, 29, 30, 3, 3, 1, 0, '', '');
INSERT INTO section VALUES (43, 'Manage preferences', 'user/preference', '1', 43, 35, 4, 31, 32, 4, 3, 1, 0, '', '');
INSERT INTO section VALUES (45, 'My Account', 'user/account', '1', 45, 4, 4, 34, 41, 5, 2, 1, 0, '', '');
INSERT INTO section VALUES (47, 'View Profile', 'user/account/action/viewProfile', '1', 47, 45, 4, 37, 38, 2, 3, 1, 0, '', '');
INSERT INTO section VALUES (49, 'Edit Preferences', 'user/userpreference', '1', 49, 45, 4, 39, 40, 3, 3, 1, 0, '', '');
INSERT INTO section VALUES (53, 'Manage modules', 'default/module', '1', 53, 13, 4, 3, 4, 1, 3, 1, 0, '', '');
INSERT INTO section VALUES (59, 'Manage blocks', 'block/block', '1', 59, 25, 4, 21, 22, 1, 3, 1, 0, '', '');
INSERT INTO section VALUES (61, 'Summary', 'user/account', '1', 61, 45, 4, 35, 36, 1, 3, 1, 0, '', '');
INSERT INTO section VALUES (63, 'PEAR packages', 'default/pear', '1', 63, 13, 4, 13, 14, 6, 3, 1, 0, '', '');
INSERT INTO section VALUES (65, 'Administrator', 'uriNode:13', '1', 65, 2, 2, 6, 7, 3, 2, 1, 0, '', '');



-- CREATE SEQUENCE section_seq;


