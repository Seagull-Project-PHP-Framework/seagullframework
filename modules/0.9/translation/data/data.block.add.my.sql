INSERT INTO `block` VALUES ({SGL_NEXT_ID}, 'Navigation_Block_Navigation', 'Translator Menu', '', '', 1, 'AdminNav', 1, 0, 'a:9:{s:15:"startParentNode";s:1:"5";s:10:"startLevel";s:1:"0";s:14:"levelsToRender";s:1:"0";s:9:"collapsed";s:1:"1";s:10:"showAlways";s:1:"1";s:12:"cacheEnabled";s:1:"1";s:11:"breadcrumbs";s:1:"0";s:8:"renderer";s:14:"SimpleRenderer";s:8:"template";s:0:"";}');


-- get the block IDs
SELECT @translatorNavBlockId := block_id FROM block WHERE title = 'Translator Menu';



INSERT INTO block_assignment VALUES (@translatorNavBlockId, 0);

INSERT INTO block_role VALUES (@translatorNavBlockId, 3);