--
-- Dumping data for table module
--

INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'publisher', 'Publisher', 'The ''Publisher'' module allows you to create content and publish it to your site.  Currently you can create various types of articles and upload and categorise any filetype, matching the two together in a browsable archive format.', 'publisher/article', 'publisher.png');

--
-- Dumping data for table permission
--

INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_add', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_insert', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_edit', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_update', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_delete', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'contenttypemgr_list', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'wikiscrapemgr', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'wikiscrapemgr_list', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articleviewmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_setDownload', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_view', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'documentmgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_download', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_downloadZipped', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'filemgr_view', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articleviewmgr_view', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articleviewmgr_summary', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_add', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_edit', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_changeStatus', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_view', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'articlemgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_insert', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_update', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_list', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_delete', '', (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_reorder', NULL, (SELECT MAX(module_id) FROM module));
INSERT INTO permission VALUES ({SGL_NEXT_ID}, 'categorymgr_reorderUpdate', NULL, (SELECT MAX(module_id) FROM module));

-- guest role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'articleviewmgr_summary'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'articleviewmgr_view'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'filemgr_download'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'filemgr_downloadZipped'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'filemgr_view'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 0, (SELECT permission_id FROM permission WHERE name = 'wikiscrapemgr_list'));

-- member role perms
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'articlemgr_add'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'articlemgr_edit'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'articlemgr_insert'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'articlemgr_list'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'articlemgr_update'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'articlemgr_view'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'articleviewmgr_summary'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'articleviewmgr_view'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'filemgr_download'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'filemgr_downloadZipped'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'filemgr_view'));
INSERT INTO role_permission VALUES ({SGL_NEXT_ID}, 2, (SELECT permission_id FROM permission WHERE name = 'wikiscrapemgr_list'));

--
-- Dumping data for table document_type
--

INSERT INTO document_type VALUES (1, 'MS Word');
INSERT INTO document_type VALUES (2, 'MS Excel');
INSERT INTO document_type VALUES (3, 'MS Powerpoint');
INSERT INTO document_type VALUES (4, 'URL');
INSERT INTO document_type VALUES (5, 'Image');
INSERT INTO document_type VALUES (6, 'PDF');
INSERT INTO document_type VALUES (7, 'unknown');

--
-- Dumping data for table item_type
--

INSERT INTO item_type VALUES (1, 'All');
INSERT INTO item_type VALUES (2, 'Html Article');
INSERT INTO item_type VALUES (4, 'News Item');
INSERT INTO item_type VALUES (5, 'Static Html Article');

--
-- Dumping data for table item_type_mapping
--

INSERT INTO item_type_mapping VALUES (3, 2, 'title', 0);
INSERT INTO item_type_mapping VALUES (4, 2, 'bodyHtml', 2);
INSERT INTO item_type_mapping VALUES (5, 4, 'title', 0);
INSERT INTO item_type_mapping VALUES (6, 4, 'newsHtml', 2);
INSERT INTO item_type_mapping VALUES (7, 5, 'title', 0);
INSERT INTO item_type_mapping VALUES (8, 5, 'bodyHtml', 2);

--
-- Creating sequences
-- sequence must start on the first free record id
--

CREATE SEQUENCE document_type_seq START WITH 8;
CREATE SEQUENCE item_type_seq START WITH 6;
CREATE SEQUENCE item_type_mapping_seq START WITH 9;
