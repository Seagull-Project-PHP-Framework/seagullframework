-- get block IDs
SELECT @langSwitcher2BlockId := block_id FROM block WHERE name = 'Default_Block_LangSwitcher2';

-- delete assignments
DELETE FROM `block_assignment` WHERE block_id = @langSwitcher2BlockId;

-- delete role assignments
DELETE FROM `block_role` WHERE block_id = @langSwitcher2BlockId;

-- delete blocks
DELETE FROM `block` WHERE block_id = @langSwitcher2BlockId;