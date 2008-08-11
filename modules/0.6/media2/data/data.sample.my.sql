/*==============================================================*/
/* Table: media_type                                            */
/*==============================================================*/
INSERT INTO `media_type` VALUES ({SGL_NEXT_ID}, 'profile','profile image');
INSERT INTO `media_type` VALUES ({SGL_NEXT_ID}, 'album', 'album image');
SELECT @mediaTypeId_profile := `media_type_id` FROM `media_type` WHERE `name` = 'profile';
SELECT @mediaTypeId_album := `media_type_id` FROM `media_type` WHERE `name` = 'album';

SELECT @mediaMimeId_gif := `media_mime_id` FROM `media_mime` WHERE `name` = 'gif image';
SELECT @mediaMimeId_jpg := `media_mime_id` FROM `media_mime` WHERE `name` = 'jpeg image';
SELECT @mediaMimeId_png := `media_mime_id` FROM `media_mime` WHERE `name` = 'png image';
SELECT @mediaMimeId_swf := `media_mime_id` FROM `media_mime` WHERE `name` = 'swf flash';
SELECT @mediaMimeId_flv := `media_mime_id` FROM `media_mime` WHERE `name` = 'flv video';

/*==============================================================*/
/* Table: media_type-mime                                       */
/*==============================================================*/
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_gif);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_jpg);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_profile, @mediaMimeId_png);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_album, @mediaMimeId_gif);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_album, @mediaMimeId_jpg);
INSERT INTO `media_type-mime` VALUES (@mediaTypeId_album, @mediaMimeId_png);