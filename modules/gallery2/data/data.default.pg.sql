-- Last edited: Pierpaolo Toniolo 29-03-2006
-- Data dump for /gallery2

BEGIN;


INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'gallery2', 'Gallery', 'Use the ''Gallery'' to manage image albums and galleries.', 'gallery2', 'publisher.png', '', NULL, NULL, NULL);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'gallery2mgr', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'gallery2mgr_cmd_list', '', (SELECT max(module_id) FROM module));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'gallery2mgr_cmd_list'));


COMMIT;

