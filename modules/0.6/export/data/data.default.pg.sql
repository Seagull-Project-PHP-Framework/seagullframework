-- Last edited: Antonio J. Garcia 2007-04-24
-- Data dump for /export
-- leave subqueries on a single line in order that table prefixes works
BEGIN;


INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'export', 'Export Data', 'Used for exporting to various formats, ie RSS, OPML, etc.', 'export/rss', 'rndmsg.png', '', NULL, NULL, NULL);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rssmgr', NULL, (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rssmgr_cmd_news', '', (
    SELECT max(module_id) FROM module
    ));


INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rss2mgr', '', (
    SELECT max(module_id) FROM module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'rss2mgr_cmd_list', '', (
    SELECT max(module_id) FROM module
    ));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'rssmgr_cmd_news'
    ));


COMMIT;

