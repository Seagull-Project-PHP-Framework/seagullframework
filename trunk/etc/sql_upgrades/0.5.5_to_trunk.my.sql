-- Andrey Podshivalov, 08 February 2006

-- rename block.content column to block.params
ALTER TABLE `block` CHANGE `content` `params` LONGTEXT NULL ;

-- add block.is_cached column
ALTER TABLE `block` ADD COLUMN `is_cached` SMALLINT NULL AFTER `is_enabled` ;

-- fill block.is_cached column
UPDATE `block` SET `is_cached` = 1 ;

-- Demian Turner, 09 March 2006

-- add module.maintainers column
ALTER TABLE `module` ADD COLUMN `maintainers` TEXT AFTER `icon` ;

-- add module.version column
ALTER TABLE `module` ADD COLUMN `version` VARCHAR(8) NULL AFTER `maintainers` ;

-- add module.license column
ALTER TABLE `module` ADD COLUMN `license` VARCHAR(16) NULL AFTER `version` ;

-- add module.state column
ALTER TABLE `module` ADD COLUMN `state` VARCHAR(8) NULL AFTER `license` ;
