-- Last edited: Pierpaolo Toniolo 29-03-2006
-- Data dump for /modules/block

BEGIN;

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'block', 'Blocks', 'Use the ''Blocks'' module to configure the contents of the blocks in the left and right hand columns.', 'block/block', '48/module_block.png', '', NULL, NULL, NULL);

--
-- perms
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr', 'Permission to use block manager', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_cmd_add', 'Permission to add new block', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_cmd_edit', 'Permission to edit existing block', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_cmd_delete', 'Permission to remove block', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_cmd_reorder', 'Permission to reorder blocks', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_cmd_list', 'Permission to view block listing', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_cmd_insert', 'Permission to view block listing', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'blockmgr_cmd_update', 'Permission to view block listing', (SELECT max(module_id) FROM module));

--
-- Dumping data for table block
--

INSERT INTO block VALUES ({SGL_NEXT_ID}, 'Navigation_Block_Navigation', 'Admin menu', '', '', 1, 'AdminNav', 1, 0, 'a:9:{s:15:"startParentNode";s:1:"4";s:10:"startLevel";s:1:"0";s:14:"levelsToRender";s:1:"0";s:9:"collapsed";s:1:"1";s:10:"showAlways";s:1:"1";s:12:"cacheEnabled";s:1:"1";s:11:"breadcrumbs";s:1:"0";s:8:"renderer";s:14:"SimpleRenderer";s:8:"template";s:0:"";}');
INSERT INTO block VALUES ({SGL_NEXT_ID}, 'Navigation_Block_Navigation', 'User menu', '', '', 1, 'MainNav', 1, 0, 'a:9:{s:15:"startParentNode";s:1:"2";s:10:"startLevel";s:1:"0";s:14:"levelsToRender";s:1:"0";s:9:"collapsed";s:1:"1";s:10:"showAlways";s:1:"1";s:12:"cacheEnabled";s:1:"1";s:11:"breadcrumbs";s:1:"0";s:8:"renderer";s:14:"SimpleRenderer";s:8:"template";s:0:"";}');
INSERT INTO block VALUES ({SGL_NEXT_ID}, 'Navigation_Block_CategoryNav', 'Categories', '', 'navWidget', 1, 'AdminCategory', 1, 1, 'N;');
--
-- Dumping data for table block_assignment
--

-- admin menu
INSERT INTO block_assignment VALUES ({SGL_NEXT_ID}, 0);
-- user menu
INSERT INTO block_assignment VALUES ({SGL_NEXT_ID}, 0);
-- adminCategoryNav (assigned in Publishing/Articles/Categories/Files sections)
INSERT INTO block_assignment VALUES (3, 67);
INSERT INTO block_assignment VALUES (3, 69);
INSERT INTO block_assignment VALUES (3, 71);
INSERT INTO block_assignment VALUES (3, 73);

--
-- Dumping data for table block_role
--

-- admin menu
INSERT INTO block_role VALUES ({SGL_NEXT_ID}, 1);
-- user menu
INSERT INTO block_role VALUES ({SGL_NEXT_ID}, -2);
-- adminCategoryNav
INSERT INTO block_role VALUES ({SGL_NEXT_ID}, 1);

COMMIT;
