INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'navigation', 'Navigation', 'The ''Navigation'' module is what you use to build your site navigation, it creates menus that you can customise in terms of look and feel, and allows you to link to any site resource.', 'navigation/page', 'navigation.png');

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


#INSERT INTO `section` VALUES (0, 'none', '', '0', 0, 0, 0, 1, 2, 0, 1, 0, 0, '', '');
#INSERT INTO `section` VALUES (45, 'Blocks', 'block/block', '1', 45, 22, 22, 16, 17, 4, 2, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (22, 'Admin menu', 'uriEmpty:', '1', 22, 0, 22, 1, 44, 2, 1, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (49, 'Manage menu', 'navigation/section', '1', 49, 47, 22, 13, 14, 1, 3, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (164, 'Manage permissions', 'user/permission', '1', 164, 158, 22, 31, 32, 2, 3, 1, 0, '', '');
#INSERT INTO `section` VALUES (176, 'Maintenance', 'default/maintenance', '1', 176, 29, 22, 7, 8, 2, 3, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (158, 'Users and security', 'user/user', '1', 158, 22, 22, 28, 37, 6, 2, 1, 0, '', '');
#INSERT INTO `section` VALUES (29, 'Global', 'default/module', '1', 29, 22, 22, 4, 9, 2, 2, 1, 0, '', '');
#INSERT INTO `section` VALUES (25, 'Configuration', 'default/config', '1', 25, 29, 22, 5, 6, 1, 3, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (8, 'My Account', 'user/account', '1', 0, 1, 1, 4, 5, 2, 2, 1, 0, '', '');
#INSERT INTO `section` VALUES (162, 'Home', 'default/default', '1', 162, 22, 22, 2, 3, 1, 2, 1, 0, '', '');
#INSERT INTO `section` VALUES (47, 'Navigation', 'navigation/section', '1', 47, 22, 22, 12, 15, 3, 2, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (168, 'Manage roles', 'user/role', '1', 168, 158, 22, 33, 34, 3, 3, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (1, 'User menu', 'default/default', '-2', NULL, 0, 1, 1, 10, 1, 1, 1, NULL, '1', NULL);
#INSERT INTO `section` VALUES (41, 'Home', '', '-2', 41, 1, 1, 2, 3, 1, 2, 1, 0, '', '');
#INSERT INTO `section` VALUES (172, 'Manage preferences', 'user/preference', '1', 172, 158, 22, 35, 36, 4, 3, 1, 0, '', '');
#INSERT INTO `section` VALUES (27, 'Publication', 'publisher/article', '1', 27, 22, 22, 18, 27, 5, 2, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (69, 'Categories', 'navigation/category', '1', 69, 27, 22, 21, 22, 2, 3, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (174, 'Manage users', 'user/user', '1', 174, 158, 22, 29, 30, 1, 3, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (73, 'Documents', 'publisher/document', '1', 73, 27, 22, 23, 24, 3, 3, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (188, 'Modules', 'default/module/action/overview', '1', 188, 1, 1, 6, 7, 3, 2, 1, 0, '', '');
#INSERT INTO `section` VALUES (190, 'Config', 'default/config', '1', 190, 1, 1, 8, 9, 4, 2, 1, 0, '', '');
#INSERT INTO `section` VALUES (192, 'Module Generator', 'default/modulegeneration', '1', 192, 29, 22, 9, 10, 4, 3, 1, 0, '', '');
#INSERT INTO `section` VALUES (32, 'Articles', 'publisher/article', '1', 32, 27, 22, 19, 20, 1, 3, 1, NULL, NULL, NULL);
#INSERT INTO `section` VALUES (194, 'My Account', 'user/account', '1', 194, 22, 22, 38, 43, 7, 2, 1, 0, '', '');
#INSERT INTO `section` VALUES (196, 'View Profile', 'user/account/action/viewProfile', '1', 196, 194, 22, 39, 40, 1, 3, 1, 0, '', '');
#INSERT INTO `section` VALUES (198, 'Edit Preferences', 'user/userpreference', '1', 198, 194, 22, 41, 42, 2, 3, 1, 0, '', '');
#INSERT INTO `section` VALUES (200, 'Translation', 'default/translation', '1', 200, 29, 22, 11, 12, 5, 3, 1, NULL, NULL, NULL);
#INSERT INTO section VALUES (186, 'Content types', 'publisher/contenttype', '1', 186, 27, 22, 25, 26, 4, 3, 1, 0, '', '');



INSERT INTO `section` VALUES (2, 'User menu', 'uriEmpty:', '-2', 2, 0, 2, 1, 6, 1, 1, 1, 0, '1', '');
INSERT INTO `section` VALUES (4, 'Admin menu', 'uriEmpty:', '1', 4, 0, 4, 1, 42, 2, 1, 1, 0, '', '');
INSERT INTO `section` VALUES (9, 'My Account', 'user/account', '2', 9, 2, 2, 4, 5, 2, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (6, 'Home', 'default/default', '-2', 6, 2, 2, 2, 3, 1, 2, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (11, 'Home', 'default/default', '1', 11, 4, 4, 2, 3, 1, 2, 0, 0, '', '');
INSERT INTO `section` VALUES (13, 'Modules', 'default/module', '1', 13, 4, 4, 4, 13, 2, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (15, 'Configuration', 'default/config', '1', 15, 13, 4, 5, 6, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (17, 'Maintenance', 'default/maintenance', '1', 17, 13, 4, 7, 8, 2, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (19, 'Module Generator', 'default/modulegeneration', '1', 19, 13, 4, 9, 10, 3, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (21, 'Translation', 'default/translation', '1', 21, 13, 4, 11, 12, 4, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (23, 'Navigation', 'navigation/section', '1', 23, 4, 4, 14, 15, 3, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (25, 'Blocks', 'block/block', '1', 25, 4, 4, 16, 17, 4, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (27, 'Publishing', 'publisher/article', '1', 27, 4, 4, 18, 25, 5, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (29, 'Articles', 'publisher/article', '1', 29, 27, 4, 19, 20, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (31, 'Categories', 'navigation/category', '1', 31, 27, 4, 21, 22, 2, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (33, 'Files', 'publisher/document', '1', 33, 27, 4, 23, 24, 3, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (35, 'Users and security', 'user/user', '1', 35, 4, 4, 26, 35, 6, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (37, 'Manage users', 'user/user', '1', 37, 35, 4, 27, 28, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (39, 'Manage permissions', 'user/permission', '1', 39, 35, 4, 29, 30, 2, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (41, 'Manage roles', 'user/role', '1', 41, 35, 4, 31, 32, 3, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (43, 'Manage preferences', 'user/preference', '1', 43, 35, 4, 33, 34, 4, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (45, 'My Account', 'user/account', '1', 45, 4, 4, 36, 41, 7, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (47, 'View Profile', 'user/account/action/viewProfile', '1', 47, 45, 4, 37, 38, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (49, 'Edit Preferences', 'user/userpreference', '1', 49, 45, 4, 39, 40, 2, 3, 1, 0, '', '');
