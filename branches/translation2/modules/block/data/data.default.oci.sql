--
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'block', 'Blocks', 'Use the ''Blocks'' module to configure the contents of the blocks in the left and right hand columns.', 'block/block', 'blocks.png');

--
-- Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr', 'Permission to use block manager', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_add', 'Permission to add new block', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_edit', 'Permission to edit existing block', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_delete', 'Permission to remove block', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_reorder', 'Permission to reorder blocks', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_list', 'Permission to view block listing', (SELECT MAX(module_id) FROM module));
