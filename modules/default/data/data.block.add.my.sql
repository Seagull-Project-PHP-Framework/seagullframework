-- LangSwitcher2
INSERT INTO `block` VALUES ({SGL_NEXT_ID}, 'Default_Block_LangSwitcher2', 'Language switcher', NULL, NULL, 1, 'BodyTop', 1, 1, 'N;');

-- block IDs
SELECT @langSwitcher2BlockId := block_id FROM block WHERE name = 'Default_Block_LangSwitcher2';

-- create block assignments
INSERT INTO `block_assignment` VALUES (@langSwitcher2BlockId, 0);

-- create block role assignments
INSERT INTO `block_role` VALUES (@langSwitcher2BlockId, -2);