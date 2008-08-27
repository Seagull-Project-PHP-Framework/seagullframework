SELECT @rootId   := 1;
SELECT @memberId := 2;

-- add address for member user
-- INSERT INTO `address` VALUES (1, NULL, NULL, NULL, NULL, NULL, NULL);
-- SELECT @addressId := MAX(address_id) FROM `address`;
-- UPDATE `usr` SET `address_id` = @addressId WHERE `usr_id` = @rootId;

-- add address for root user
-- INSERT INTO `address` VALUES (2, NULL, NULL, NULL, NULL, NULL, NULL);
-- SELECT @addressId := MAX(address_id) FROM `address`;
-- UPDATE `usr` SET `address_id` = @addressId WHERE `usr_id` = @memberId;