-- Last edited: Pierpaolo Toniolo 29-03-2006
-- Data dump for /export

BEGIN;


INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'export', 'Export Data', 'Used for exporting to various formats, ie RSS, OPML, etc.', 'export/rss', 'rndmsg.png', '', NULL, NULL, NULL);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rssmgr', NULL, (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rssmgr_cmd_news', '', (SELECT max(module_id) FROM module));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'rssmgr_cmd_news'));


COMMIT;

