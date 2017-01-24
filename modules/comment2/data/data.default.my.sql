INSERT INTO `module` VALUES ({SGL_NEXT_ID}, 1, 'comment2', 'Comments2', 'Allows users to associate comments with any module in the system.', '', '48/module_block.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'examplemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'examplemgr_cmd_list', '', @moduleId);
