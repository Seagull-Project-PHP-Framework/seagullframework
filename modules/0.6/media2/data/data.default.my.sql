INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'media2', 'Media2', NULL, NULL, NULL, 'Thomas Goetz', NULL, NULL, NULL);

-- SELECT @moduleId := MAX(module_id) FROM module;
-- SELECT @memberId := 2;
-- SELECT @anyRole  := -2;

/*==============================================================*/
/* Table: media_mime                                            */
/*==============================================================*/
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'gif image', 'gif', 'image/gif', '47 49 46 38');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'jpeg image', 'jpg', 'image/jpeg', 'FF D8 FF');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'png image', 'png', 'image/png', '89 50 4E 47 0D 0A 1A 0A 00 00 00 0D 49 48 44 52');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'swf flash', 'swf', 'application/x-shockwave-flash', '46 57 53');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'flv video', 'flv', 'video/x-flv', '46 4C 56 01');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'pdf document', 'pdf', 'application/pdf', '25 50 44 46 2D 31 2E');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'flv video', 'doc', 'application/msword', 'D0 CF 11 E0 A1 B1 1A E1');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'zip archive', 'zip', 'application/zip', '50 4B 03 04');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'mpeg video', 'mpg', 'video/mpeg', '00 00 01 BA 21 00 01');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'plain file', 'txt', 'text/plain', '');