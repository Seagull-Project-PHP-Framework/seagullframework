INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'translation', 'Translation', 'Utilities to translate your application', 'translation/translation', '48/module_default.png', 'Julien Casanova', '0.1', 'BSD', 'beta');

-- add perms
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'jstranslationmgr'', '', (
    SELECT MAX(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'jstranslationmgr_cmd_list', '', (
    SELECT MAX(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'jstranslationmgr_cmd_cliResult', '', (
    SELECT MAX(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'jstranslationmgr_cmd_createFiles', '', (
    SELECT MAX(module_id) FROM module
    ));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr', '', (
    SELECT MAX(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr_cmd_list', '', (
    SELECT MAX(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr_cmd_edit', '', (
    SELECT MAX(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr_cmd_update', '', (
    SELECT MAX(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr_cmd_summary', '', (
    SELECT MAX(module_id) FROM module
    ));