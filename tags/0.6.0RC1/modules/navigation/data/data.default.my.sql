INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'navigation', 'Navigation', 'The ''Navigation'' module is what you use to build your site navigation, it creates menus that you can customise in terms of look and feel, and allows you to link to any site resource.', 'navigation/page', 'navigation.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr_cmd_changeStyle', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_reorder', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'sectionmgr_cmd_list', '', @moduleId);

#
# Dumping data for table `section`
#

INSERT INTO `section` VALUES (0, 'root', 'uriEmpty:', '1', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `section` VALUES (2, 'User menu', 'uriEmpty:', '-2', 2, 0, 2, 1, 6, 1, 1, 1, 0, '', '');
INSERT INTO `section` VALUES (4, 'Admin menu', 'uriEmpty:', '1', 4, 0, 4, 1, 50, 2, 1, 1, 0, '', '');
INSERT INTO `section` VALUES (9, 'My Account', 'user/account', '2', 9, 2, 2, 4, 5, 2, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (6, 'Home', 'default/default', '-2', 6, 2, 2, 2, 3, 1, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (51, 'Manage navigation', 'navigation/section', '1', 51, 23, 4, 17, 18, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (13, 'General', 'default/module', '1', 13, 4, 4, 2, 15, 1, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (15, 'Configuration', 'default/config', '1', 15, 13, 4, 5, 6, 2, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (17, 'Maintenance', 'default/maintenance', '1', 17, 13, 4, 7, 8, 3, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (19, 'Module Generator', 'default/modulegeneration', '1', 19, 13, 4, 9, 10, 4, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (21, 'Translation', 'default/translation', '1', 21, 13, 4, 11, 12, 5, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (23, 'Navigation', 'navigation/section', '1', 23, 4, 4, 16, 19, 2, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (25, 'Blocks', 'block/block', '1', 25, 4, 4, 20, 23, 3, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (27, 'Publishing', 'publisher/article', '1', 27, 4, 4, 24, 31, 4, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (29, 'Articles', 'publisher/article', '1', 29, 27, 4, 25, 26, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (31, 'Categories', 'navigation/category', '1', 31, 27, 4, 27, 28, 2, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (33, 'Files', 'publisher/document', '1', 33, 27, 4, 29, 30, 3, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (35, 'Users and security', 'user/user', '1', 35, 4, 4, 32, 41, 5, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (37, 'Manage users', 'user/user', '1', 37, 35, 4, 33, 34, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (39, 'Manage permissions', 'user/permission', '1', 39, 35, 4, 35, 36, 2, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (41, 'Manage roles', 'user/role', '1', 41, 35, 4, 37, 38, 3, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (43, 'Manage preferences', 'user/preference', '1', 43, 35, 4, 39, 40, 4, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (45, 'My Account', 'user/account', '1', 45, 4, 4, 42, 49, 6, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (47, 'View Profile', 'user/account/action/viewProfile', '1', 47, 45, 4, 45, 46, 2, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (49, 'Edit Preferences', 'user/userpreference', '1', 49, 45, 4, 47, 48, 3, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (53, 'Manage modules', 'default/module', '1', 53, 13, 4, 3, 4, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (59, 'Manage blocks', 'block/block', '1', 59, 25, 4, 21, 22, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (61, 'Summary', 'user/account', '1', 61, 45, 4, 43, 44, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (63, 'PEAR packages', 'default/pear', '1', 63, 13, 4, 13, 14, 6, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (65, 'Administrator', 'uriNode:13', '1', 65, 2, 2, 6, 7, 3, 2, 1, 0, '', '');
