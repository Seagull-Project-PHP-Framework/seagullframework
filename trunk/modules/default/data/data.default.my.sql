# Generation Time: Nov 18, 2004 at 05:07 PM
# Server version: 3.23.58
# PHP Version: 5.0.2
# Database : `seagull`


#
# Dumping data for table `module`
#
INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'default', 'Default', 'The ''Default'' module includes functionality that is needed in every install, for example, configuration and interface language manangement, and module management.', NULL, 'default.png');
INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'maintenance', 'Maintenance', 'The ''Maintenance'' module lets you take care of several application maintenance tasks, like cleaning up temporary files, managing interface language translations, rebuilding DataObjects files, etc.', 'maintenance/maintenance', 'maintenance.png');
INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'navigation', 'Navigation', 'The ''Navigation'' module is what you use to build your site navigation, it creates menus that you can customise in terms of look and feel, and allows you to link to any site resource.', 'navigation/page', 'navigation.png');
INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'user', 'Users and Security', 'The ''Users and Security'' module allows you to manage all your users, administer the roles they belong to, change their passwords, setup permissions and alter the global default preferences.', 'user/user', 'users.png');