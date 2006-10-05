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

# some images for testings
INSERT INTO `media` (`media_id`, `file_type_id`, `name`, `file_name`, `file_size`, `mime_type`, `date_created`, `added_by`, `description`, `num_times_downloaded`) VALUES (1, 5, 'demo_building_1.jpg', '1949219608d3fb6f09012a522c1873a3.jpg', 13340, 'image/jpeg', '2006-09-08 13:20:01', 1, '', NULL),
(2, 5, 'demo_building_2.jpg', '0046994b6c45437e5471658c070bf1d4.jpg', 17251, 'image/jpeg', '2006-09-07 13:20:16', 1, '', NULL),
(3, 5, 'demo_hands.jpg', 'a7ed16c0e3258c8a11e7ee5cb9c675bb.jpg', 12949, 'image/jpeg', '2006-08-29 14:49:28', 1, '', NULL),
(4, 5, 'Poignée de main', '0126d73b6c53574ff2270097c1c30df9.jpg', 8058, 'image/jpeg', '2006-08-25 16:51:15', 0, 'Rien à dire', NULL),
(5, 5, 'demo_handshake_2.jpg', 'fd386a28e5a31fe9016a83a941261439.jpg', 9209, 'image/jpeg', '2006-08-12 16:51:42', 1, '', NULL),
(6, 5, 'demo_horloge.jpg', 'dbf1d40da6d959f63a20b923639029f5.jpg', 49822, 'image/jpeg', '2006-08-05 16:52:00', 1, '', NULL),
(7, 5, 'moi petit', '56fec1313edc8ec9bc41626fd690a3af.jpg', 82812, 'image/jpeg', '2006-09-22 16:52:24', 1, '', NULL),
(8, 5, 'demo_keyboard_1.jpg', '3b467b2c5be26662ee035638ccd64858.jpg', 17263, 'image/jpeg', '2006-09-22 16:52:41', 1, '', NULL),
(9, 7, 'Sample_Word.doc', 'db1c2fa33d3a48a84a4ef06b424852e4', 10752, 'application/octet-stream', '2006-09-22 16:55:00', 1, '', NULL),
(10, 7, 'Document word', '9ee73dc771042bb0321bbabf75613644', 13824, 'application/octet-stream', '2006-09-22 16:55:29', 0, 'Je ne sais pas ce qui se passe mais les extensions ne sont pas reconnues comme il faudrait.', NULL),
(11, 7, 'Sample.doc', '5d24200f55a03f02b8dac4255f604849', 12800, 'application/octet-stream', '2006-09-22 16:55:41', 1, '', NULL),
(12, 5, 'Hiver.jpg', '60640c7c86b4e0f79be55d7d710e2720.jpg', 105542, 'image/jpeg', '2006-10-04 18:06:23', 0, '', NULL);