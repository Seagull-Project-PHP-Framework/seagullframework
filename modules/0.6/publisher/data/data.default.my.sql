INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'publisher', 'Publisher', 'The ''Publisher'' module allows you to create content and publish it to your site.  Currently you can create various types of articles and upload and categorise any filetype, matching the two together in a browsable archive format.  This module is not compatible with the ''Media'' module.', 'publisher/article', '48/module_publisher.png', 'AJ Tarachanowicz', NULL, 'BSD', 'beta');

SELECT @moduleId := MAX(module_id) FROM module;

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_cmd_add', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_cmd_insert', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_cmd_edit', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_cmd_update', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_cmd_delete', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_cmd_list', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'wikiscrapemgr', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'wikiscrapemgr_cmd_list', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articleviewmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_cmd_setDownload', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_cmd_view', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_cmd_download', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_cmd_downloadZipped', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_cmd_view', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articleviewmgr_cmd_view', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articleviewmgr_cmd_summary', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_cmd_add', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_cmd_edit', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_cmd_changeStatus', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_cmd_view', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_cmd_insert', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_cmd_update', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_cmd_list', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_cmd_delete', '', @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_cmd_reorder', NULL, @moduleId);
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_cmd_reorderUpdate', NULL, @moduleId);

#member role perms
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_cmd_add';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_cmd_edit';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_cmd_insert';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_cmd_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_cmd_update';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articlemgr_cmd_view';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articleviewmgr_cmd_summary';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'articleviewmgr_cmd_view';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'filemgr_cmd_download';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'filemgr_cmd_downloadZipped';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'filemgr_cmd_view';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);
SELECT @permissionId := permission_id FROM permission WHERE name = 'wikiscrapemgr_cmd_list';
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, @permissionId);



#
# Dumping data for table `document_type`
#

INSERT INTO document_type VALUES (1,'MS Word');
INSERT INTO document_type VALUES (2,'MS Excel');
INSERT INTO document_type VALUES (3,'MS Powerpoint');
INSERT INTO document_type VALUES (4,'URL');
INSERT INTO document_type VALUES (5,'Image');
INSERT INTO document_type VALUES (6,'PDF');
INSERT INTO document_type VALUES (7,'unknown');


#
# Dumping data for table `item_type`
#


INSERT INTO item_type VALUES (1,'All');
INSERT INTO item_type VALUES (2,'Html Article');
INSERT INTO item_type VALUES (4,'News Item');
INSERT INTO item_type VALUES (5,'Static Html Article');

#
# Dumping data for table `item_type_mapping`
#


INSERT INTO item_type_mapping VALUES (3,2,'title',0);
INSERT INTO item_type_mapping VALUES (4,2,'bodyHtml',2);
INSERT INTO item_type_mapping VALUES (5,4,'title',0);
INSERT INTO item_type_mapping VALUES (6,4,'newsHtml',2);
INSERT INTO item_type_mapping VALUES (7,5,'title',0);
INSERT INTO item_type_mapping VALUES (8,5,'bodyHtml',2);

#
# Dumping data for table `category`
#

INSERT INTO category VALUES (1,'PublisherRoot',NULL,0,1,1,4,1,1);