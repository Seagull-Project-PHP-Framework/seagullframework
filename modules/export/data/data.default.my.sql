INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'export', 'Export Data', 'Used for exporting to various formats, ie RSS, OPML, etc.  Export is dependent on the ''Publisher'' module', 'export/rss', 'rndmsg.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rssmgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rssmgr_cmd_news', '', @moduleId);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rss2mgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rss2mgr_cmd_list', NULL, @moduleId);


#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'rssmgr_cmd_news';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);