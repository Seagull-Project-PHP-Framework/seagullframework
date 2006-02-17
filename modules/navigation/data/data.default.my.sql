INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'navigation', 'Navigation', 'The ''Navigation'' module is what you use to build your site navigation, it creates menus that you can customise in terms of look and feel, and allows you to link to any site resource.', 'navigation/page', 'navigation.png');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr_cmd_changeStyle', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pagemgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pagemgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pagemgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pagemgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pagemgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pagemgr_cmd_reorder', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pagemgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'navstylemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'pagemgr', '', @moduleId);

#
# Dumping data for table `section`
#

INSERT INTO `section` VALUES (0, 'none', '', '0', 0, 0, 0, 1, 2, 0, 1, 0, 0, '', '');
INSERT INTO `section` VALUES (45, 'Blocks', 'block/block', '1', 45, 22, 22, 16, 17, 4, 2, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (49, 'Manage menu', 'navigation/page', '1', 49, 47, 22, 13, 14, 1, 3, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (164, 'Manage permissions', 'user/permission', '1', 164, 158, 22, 29, 30, 1, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (176, 'Maintenance', 'default/maintenance', '1', 176, 29, 22, 7, 8, 2, 3, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (22, 'Admin menu', 'admin/adminmenu', '1', 22, 0, 22, 1, 38, 2, 1, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (158, 'Users and security', 'user/user', '1', 158, 22, 22, 28, 37, 6, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (29, 'Global', 'default/module', '1', 29, 22, 22, 4, 11, 2, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (25, 'Configuration', 'default/config', '1', 25, 29, 22, 5, 6, 1, 3, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (8, 'My Account', 'user/account', '1', 0, 1, 1, 4, 5, 2, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (162, 'Home', 'default/default', '1', 162, 22, 22, 2, 3, 1, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (47, 'Navigation', 'navigation/page', '1', 47, 22, 22, 12, 15, 3, 2, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (168, 'Manage roles', 'user/role', '1', 168, 158, 22, 31, 32, 2, 3, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (174, 'Manage users', 'user/user', '1', 174, 158, 22, 35, 36, 4, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (127, 'Manage Pear packages', 'default/pear', '1', 127, 29, 22, 9, 10, 3, 3, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (1, 'User menu', 'default/default', '1,2,0', NULL, 0, 1, 1, 10, 1, 1, 1, NULL, '1', NULL);
INSERT INTO `section` VALUES (41, 'Home', '', '1,2,0', 41, 1, 1, 2, 3, 1, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (172, 'Manage preferences', 'user/preference', '1', 172, 158, 22, 33, 34, 3, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (27, 'Publication', 'publisher/article', '1', 27, 22, 22, 18, 27, 5, 2, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (69, 'Categories', 'navigation/category', '1', 69, 27, 22, 19, 20, 1, 3, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (32, 'Articles', 'publisher/article', '1', 32, 27, 22, 21, 22, 2, 3, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (73, 'Documents', 'publisher/document', '1', 73, 27, 22, 23, 24, 3, 3, 1, NULL, NULL, NULL);
INSERT INTO `section` VALUES (186, 'Content types', 'publisher/contenttype', '1', 186, 27, 22, 25, 26, 4, 3, 1, 0, '', '');
INSERT INTO `section` VALUES (188, 'Modules', 'default/module/action/overview', '1', 188, 1, 1, 6, 7, 3, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (190, 'Config', 'default/config', '1', 190, 1, 1, 8, 9, 4, 2, 1, 0, '', '');
INSERT INTO `section` VALUES (192, 'Module Generator', 'default/modulegeneration/action/list', '1', 192, 29, 22, 11, 12, 4, 3, 1, 0, '', '');
