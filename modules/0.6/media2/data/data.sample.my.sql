/*==============================================================*/
/* Table: media_type                                            */
/*==============================================================*/
INSERT INTO `media_type` VALUES ({SGL_NEXT_ID}, 'profile','profile image');
INSERT INTO `media_type` VALUES ({SGL_NEXT_ID}, 'album', 'album image');
SELECT @mediaTypeId_profile := `media_type_id` FROM `media_type` WHERE `name` = 'profile';
SELECT @mediaTypeId_album := `media_type_id` FROM `media_type` WHERE `name` = 'album';

/*==============================================================*/
/* Table: media_mime                                            */
/*==============================================================*/
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'gif image', 'gif', 'image/gif', '47 49 46 38');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'jpeg image', 'jpg', 'image/jpeg', 'FF D8 FF');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'jpeg image', 'jpg', 'image/pjpeg', 'FF D8 FF');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'png image', 'png', 'image/png', '89 50 4E 47 0D 0A 1A 0A 00 00 00 0D 49 48 44 52');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'png image', 'png', 'image/x-png', '89 50 4E 47 0D 0A 1A 0A 00 00 00 0D 49 48 44 52');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'swf flash', 'swf', 'application/x-shockwave-flash', '46 57 53');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'flv video', 'flv', 'video/x-flv', '46 4C 56 01');

INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'pdf document', 'pdf', 'application/pdf', '25 50 44 46 2D 31 2E');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'flv video', 'doc', 'application/msword', 'D0 CF 11 E0 A1 B1 1A E1');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'zip archive', 'zip', 'application/zip', '50 4B 03 04');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'mpeg video', 'mpg', 'video/mpeg', '00 00 01 BA 21 00 01');
INSERT INTO `media_mime` VALUES ({SGL_NEXT_ID}, 'plain file', 'txt', 'text/plain', '');


SELECT @mediaMimeId_gif := `media_mime_id` FROM `media_mime` WHERE `name` = 'gif image';
SELECT @mediaMimeId_jpg1 := `media_mime_id` FROM `media_mime` WHERE `name` = 'jpeg image' AND `content_type` = 'image/jpeg';
SELECT @mediaMimeId_jpg2 := `media_mime_id` FROM `media_mime` WHERE `name` = 'jpeg image' AND `content_type` = 'image/pjpeg';
SELECT @mediaMimeId_png1 := `media_mime_id` FROM `media_mime` WHERE `name` = 'png image' AND `content_type` = 'image/png';
SELECT @mediaMimeId_png2 := `media_mime_id` FROM `media_mime` WHERE `name` = 'png image' AND `content_type` = 'image/x-png';
SELECT @mediaMimeId_swf := `media_mime_id` FROM `media_mime` WHERE `name` = 'swf flash';
SELECT @mediaMimeId_flv := `media_mime_id` FROM `media_mime` WHERE `name` = 'flv video';

/*==============================================================*/
/* Table: media_type-mime                                       */
/*==============================================================*/
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_gif);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_jpg1);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_jpg2);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_png1);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_png2);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_album, @mediaMimeId_gif);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_album, @mediaMimeId_jpg1);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_album, @mediaMimeId_jpg2);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_album, @mediaMimeId_png1);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_album, @mediaMimeId_png2);