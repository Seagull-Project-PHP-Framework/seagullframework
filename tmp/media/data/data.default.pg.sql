-- Last edited: Antonio J. Garcia 2007-04-24
-- Schema for media
-- leave subqueries on a single line in order that table prefixes works

BEGIN;

--
--  Dumping data for table media_type
--

INSERT INTO file_type VALUES (1,'MS Word');
INSERT INTO file_type VALUES (2,'MS Excel');
INSERT INTO file_type VALUES (3,'MS Powerpoint');
INSERT INTO file_type VALUES (4,'URL');
INSERT INTO file_type VALUES (5,'Image');
INSERT INTO file_type VALUES (6,'PDF');
INSERT INTO file_type VALUES (7,'unknown');
INSERT INTO file_type VALUES (8,'Text');
INSERT INTO file_type VALUES (9,'Zip Archive');


INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'media', 'Media Manager', 'The Media Management module allows you to store and manage media.', '', '48/module_block.png', '', NULL, NULL, NULL);

-- SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'fileassocmgr', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_add', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_insert', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_edit', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_update', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_setDownload', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_view', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_delete', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_list', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_cmd_download', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_cmd_downloadZipped', '', (
    SELECT max(module_id) from module
    ));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_cmd_view', '', (
    SELECT max(module_id) from module
    ));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'mediamgr'
    ));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (
    SELECT permission_id FROM permission WHERE name = 'fileassocmgr'
    ));

COMMIT;

