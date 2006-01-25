#module
INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'block', 'Blocks', 'Use the ''Blocks'' module to configure the contents of the blocks in the left and right hand columns.', 'block/block', '48/module_block.png');

SELECT @moduleId := MAX(module_id) FROM module;

#perms
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr', 'Permission to use block manager', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_add', 'Permission to add new block', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_edit', 'Permission to edit existing block', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_delete', 'Permission to remove block', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_reorder', 'Permission to reorder blocks', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_list', 'Permission to view block listing', @moduleId);
