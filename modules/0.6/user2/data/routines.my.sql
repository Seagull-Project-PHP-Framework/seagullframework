-- DELIMITER $$

-- DROP TRIGGER IF EXISTS `usr_insert_create_address` $$
-- CREATE TRIGGER `usr_insert_create_address` AFTER INSERT
--     ON `usr` FOR EACH ROW
-- BEGIN
--    INSERT INTO `address` VALUES (NEW.usr_id, NULL, NULL, NULL, NULL, NULL, NULL);
-- END $$

-- DELIMITER ;