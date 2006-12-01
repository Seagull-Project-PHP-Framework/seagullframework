INSERT INTO `module` VALUES ({SGL_NEXT_ID}, 1, 'comment', 'Comments', 'Allows users to associate comments with any module in the system.', '', '48/module_block.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;