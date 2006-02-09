-- Andrey Podshivalov, 08 February 2006

-- rename block.content column to block.params
ALTER TABLE `block` CHANGE `content` `params` LONGTEXT NULL ;

-- add block.is_cached column
ALTER TABLE `block` ADD COLUMN `is_cached` SMALLINT NULL AFTER `is_enabled` ;

-- fill block.is_cached column 
UPDATE `block` SET `is_cached` = 1 ;
