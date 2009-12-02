-- 
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'export', 'Export Data', 'Used for exporting to various formats, ie RSS, OPML, etc.', 'export/rss', 'rndmsg.png', '', NULL, NULL, NULL);

-- 
-- Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rssmgr', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rssmgr_cmd_news', '', (SELECT MAX(module_id) FROM module));

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rss2mgr', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rss2mgr_cmd_list', NULL, (SELECT MAX(module_id) FROM module));

-- guest role perms
-- INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'rssmgr_cmd_news'));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'rssmgr_cmd_news'));