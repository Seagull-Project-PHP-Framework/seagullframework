#
# Dumping data for table `file_type`
#

INSERT INTO file_type VALUES (1,'MS Word');
INSERT INTO file_type VALUES (2,'MS Excel');
INSERT INTO file_type VALUES (3,'MS Powerpoint');
INSERT INTO file_type VALUES (4,'URL');
INSERT INTO file_type VALUES (5,'Image');
INSERT INTO file_type VALUES (6,'PDF');
INSERT INTO file_type VALUES (7,'unknown');
INSERT INTO file_type VALUES (8,'Text');
INSERT INTO file_type VALUES (9,'Zip Archive');


INSERT INTO `module` VALUES ({SGL_NEXT_ID}, 1, 'media', 'Media Manager', 'The Media Management module allows you to store and manage media.', '', '48/module_block.png', '', NULL, NULL, NULL);

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'fileassocmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_setDownload', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_view', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'mediamgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_cmd_download', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_cmd_downloadZipped', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_cmd_view', '', @moduleId);


#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'mediamgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'fileassocmgr';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);