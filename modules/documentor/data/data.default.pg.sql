-- Last edited: Pierpaolo Toniolo 29-03-2006
-- Data dump for /documentor

BEGIN;


INSERT INTO module VALUES ({SGL_NEXT_ID}, 0, 'documentor', 'Documentor', '''Documentor'' is a module that lets you quickly and easily create documentation in html format based on articles you submit in the ''Publisher'' module.', NULL, 'documentor.png', '', NULL, NULL, NULL);

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentormgr', '', (SELECT max(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentormgr_cmd_list', '', (SELECT max(module_id) FROM module));


COMMIT;

