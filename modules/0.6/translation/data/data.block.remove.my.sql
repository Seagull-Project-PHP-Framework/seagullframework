-- get block IDs
SELECT @translatorNavBlockId := block_id FROM block WHERE title = 'Translator Menu';

-- delete assignments
DELETE FROM `block_assignment` WHERE block_id = @translatorNavBlockId;

-- delete role assignments
DELETE FROM `block_role` WHERE block_id = @translatorNavBlockId;

-- delete blocks
DELETE FROM `block` WHERE block_id = @translatorNavBlockId;
