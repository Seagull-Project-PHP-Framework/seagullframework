INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'translation', 'Translation', 'Utilities to translate your application', 'translation/translation', '48/module_default.png', 'Julien Casanova', '0.1', 'BSD', 'beta');

SELECT @moduleId := MAX(module_id) FROM module;

-- add perms

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'jstranslationmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'jstranslationmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'jstranslationmgr_cmd_cliResult', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'jstranslationmgr_cmd_createFiles', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'translationmgr_cmd_summary', '', @moduleId);