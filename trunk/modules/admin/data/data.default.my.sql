INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'admin', 'Admin', 'The ''Admin'' module is what you use to setup your admin interface. Currently, only manages admin navigation', 'admin/adminmenu', '48/module_admin.png');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminmenumgr_list', 'Browse admin menu items', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminmenumgr_add', 'Add an item to admin menu', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminmenumgr_insert', 'Add an item to admin menu', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminmenumgr_edit', 'Edit an item from admin menu', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminmenumgr_update', 'Edit an item from admin menu', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminmenumgr_delete', 'Delete an item from admin menu', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminmenumgr_reorder', 'Reorder admin menu', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'adminmenumgr', 'Global rights to manage admin menu', @moduleId);
#
# Dumping data for table `admin_menu`
#

INSERT INTO `admin_menu` VALUES (0, 'None', NULL, '0', 0, 0, 1, 2, 1, 0, 0, 0);
INSERT INTO `admin_menu` VALUES (1, 'Gestion du site', NULL, '1,2', 0, 1, 1, 10, 1, 1, 1, 0);
INSERT INTO `admin_menu` VALUES (4, 'Configuration', 'default/config', '1,2', 1, 1, 2, 3, 2, 1, 1, 1);
INSERT INTO `admin_menu` VALUES (5, 'Choix de la langue', NULL, '1,2', 1, 1, 6, 7, 2, 3, 1, 0);
INSERT INTO `admin_menu` VALUES (6, 'Mes préférences', 'user/account', '1,2', 1, 1, 4, 5, 2, 2, 1, 1);
INSERT INTO `admin_menu` VALUES (13, 'Gérer les articles', 'publisher/article', '1,2', 9, 9, 4, 5, 2, 2, 1, 1);
INSERT INTO `admin_menu` VALUES (8, 'Catégories', NULL, '1,2', 0, 8, 1, 2, 1, 2, 0, 0);
INSERT INTO `admin_menu` VALUES (9, 'Contenus', NULL, '1,2', 0, 9, 1, 12, 1, 3, 1, 0);
INSERT INTO `admin_menu` VALUES (10, 'Menus', NULL, '1,2', 0, 10, 1, 8, 1, 4, 1, 0);
INSERT INTO `admin_menu` VALUES (11, 'Menu administration', 'admin/adminmenu', '1', 10, 10, 4, 5, 2, 2, 1, 1);
INSERT INTO `admin_menu` VALUES (21, 'Types de contenu', 'publisher/contenttype', '1', 9, 9, 10, 11, 2, 5, 1, 1);
INSERT INTO `admin_menu` VALUES (19, 'Gérer les documents', 'publisher/document', '1,2', 9, 9, 6, 7, 2, 3, 1, 1);
INSERT INTO `admin_menu` VALUES (24, 'Styles de menu', 'navigation/navstyle', '1,2', 10, 10, 6, 7, 2, 3, 1, 1);
INSERT INTO `admin_menu` VALUES (22, 'Gérer le menu', 'navigation/page', '1,2', 10, 10, 2, 3, 2, 1, 1, 1);
INSERT INTO `admin_menu` VALUES (29, 'Utilisateurs', '', '1,2', 0, 29, 1, 10, 1, 5, 1, 0);
INSERT INTO `admin_menu` VALUES (25, 'Maintenance', 'default/maintenance', '1', 1, 1, 8, 9, 2, 4, 1, 1);
INSERT INTO `admin_menu` VALUES (30, 'Gérer les utilisateurs', 'user/user', '1,2', 29, 29, 2, 3, 2, 1, 1, 1);
INSERT INTO `admin_menu` VALUES (31, 'Gérer les rôles', 'user/role', '1,2', 29, 29, 4, 5, 2, 2, 1, 1);
INSERT INTO `admin_menu` VALUES (32, 'Gérer les permissions', 'user/permission', '1', 29, 29, 6, 7, 2, 3, 1, 1);
INSERT INTO `admin_menu` VALUES (33, 'Modules', NULL, '1,2', 0, 33, 1, 6, 1, 6, 1, 0);
INSERT INTO `admin_menu` VALUES (34, 'Liste des modules', 'default/module/action/overview', '1,2', 33, 33, 2, 3, 2, 1, 1, 1);
INSERT INTO `admin_menu` VALUES (37, 'Gérer les préférences utilisateur', 'user/userpreference', '1,2', 29, 29, 8, 9, 2, 4, 1, 1);
INSERT INTO `admin_menu` VALUES (45, 'Gérer les blocs', 'block/block', '1,2', 9, 9, 8, 9, 2, 4, 1, 1);
INSERT INTO `admin_menu` VALUES (12, 'Gérer les catégories', 'navigation/category/frmCatID/1', '1,2', 9, 9, 2, 3, 2, 1, 1, 1);
