INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'translation', 'Translation', 'Utilities to translate your application', 'translation/translation', '48/module_default.png', 'Julien Casanova', '0.1', 'BSD', 'beta');

-- add perms
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr', '', (
    SELECT MAX(module_id) FROM module
    ));
INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'translation', 'Translation', 'Utilities to translate your application', 'translation/translation', '48/module_default.png', 'Julien Casanova', '0.1', 'BSD', 'beta');

-- add perms
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr', '', (
    SELECT MAX(module_id) FROM module
    ));