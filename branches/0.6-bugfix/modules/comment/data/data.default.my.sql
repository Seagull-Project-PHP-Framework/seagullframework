INSERT INTO `module` VALUES ({SGL_NEXT_ID}, 1, 'comment', 'Comments', 'Allows users to associate comments with any module in the system.', '', '48/module_block.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'admincommentmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'admincommentmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'admincommentmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'admincommentmgr_cmd_changeStatus', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'admincommentmgr_cmd_delete', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'akismetmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'akismetmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'akismetmgr_cmd_reportHam', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'akismetmgr_cmd_reportSpam', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'akismetmgr_cmd_testAkismetAPIKey', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'commentmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'commentmgr_cmd_insert', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'commentsearchmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'commentsearchmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'commentsearchmgr_cmd_search', '', @moduleId);
