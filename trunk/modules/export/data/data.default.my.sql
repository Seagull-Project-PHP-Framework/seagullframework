INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'export', 'Export Data', 'Used for exporting to various formats, ie RSS, OPML, etc.', 'export/rss', 'rndmsg.png');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rssmgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rssmgr_news', '', @moduleId);

SELECT @permissionId := permission_id FROM permission WHERE name = 'rssmgr_news';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, @permissionId);