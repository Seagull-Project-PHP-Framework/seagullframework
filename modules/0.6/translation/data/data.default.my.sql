INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'translation', 'Translation', 'Utilities to translate your application', 'translation/translation', '48/module_default.png', 'Julien Casanova', '0.1', 'BSD', 'beta');

SELECT @moduleId := MAX(module_id) FROM module;

-- add perms
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr', '', @moduleId);

-- add assignments
SELECT @permissionId := permission_id FROM permission WHERE name = 'translationmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 3, @permissionId);

-- add root node for navigation that appears for 'translator' role, SGL_TRANSLATOR
INSERT INTO `section` VALUES (5, 'Translator menu', 'uriEmpty:', '-2', 5, 0, 5, 1, 2, 3, 1, 1, 0, '', '');