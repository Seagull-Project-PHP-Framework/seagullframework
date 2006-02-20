#
# Dumping data for table `block`
#
INSERT INTO `block` VALUES (1, 'Publisher_Block_SiteNews', 'Site News', '', '', 2, 'Left', 0, 1, 'N;');
INSERT INTO `block` VALUES (3, 'Default_Block_Sample1', 'SourceForge Site', '', '', 4, 'Left', 0, 1, 'N;');
INSERT INTO `block` VALUES (4, 'Default_Block_Sample2', 'Syndication', '', '', 3, 'Left', 0, 1, 'N;');
INSERT INTO `block` VALUES (5, 'Navigation_Block_CategoryNav', 'Categories', '', 'navWidget', 1, 'AdminCategory', 0, 1, 'N;');
INSERT INTO `block` VALUES (10, 'Default_Block_SampleRight1', 'Sample Right Block', '', '', 5, 'Right', 0, 1, 'N;');
INSERT INTO `block` VALUES (11, 'Default_Block_Calendar', 'Calendar', '', '', 2, 'Left', 0, 1, 'N;');
INSERT INTO `block` VALUES (12, 'User_Block_Login', 'Login', '', '', 1, 'Right', 0, 0, 'N;');
INSERT INTO `block` VALUES (17, 'Publisher_Block_RecentHtmlArticles2', 'Recent articles', '', '', 3, 'Right', 0, 1, 'N;');
INSERT INTO `block` VALUES (18, 'Navigation_Block_Navigation', 'User menu', '', '', 1, 'UserNav', 0, 1, 'a:9:{s:15:"startParentNode";s:1:"1";s:10:"startLevel";s:1:"0";s:14:"levelsToRender";s:1:"0";s:9:"collapsed";s:1:"1";s:10:"showAlways";s:1:"1";s:12:"cacheEnabled";s:1:"1";s:11:"breadcrumbs";s:1:"0";s:8:"renderer";s:9:"SimpleNav";s:8:"template";s:0:"";}');
INSERT INTO `block` VALUES (19, 'Publisher_Block_Article', 'Articles statiques', '', '', 4, 'Right', 0, 1, 'a:2:{s:9:"articleId";s:1:"2";s:8:"template";s:33:"articleViewStaticHtmlArticle.html";}');
INSERT INTO `block` VALUES (20, 'Navigation_Block_Navigation', 'Admin menu', '', '', 1, 'AdminNav', 0, 1, 'a:9:{s:15:"startParentNode";s:2:"22";s:10:"startLevel";s:1:"0";s:14:"levelsToRender";s:1:"0";s:9:"collapsed";s:1:"0";s:10:"showAlways";s:1:"1";s:12:"cacheEnabled";s:1:"1";s:11:"breadcrumbs";s:1:"0";s:8:"renderer";s:9:"SimpleNav";s:8:"template";s:0:"";}');


#
# Dumping data for table `block_assignment`
#

INSERT INTO `block_assignment` VALUES (1, 1);
INSERT INTO `block_assignment` VALUES (1, 8);
INSERT INTO `block_assignment` VALUES (1, 162);
INSERT INTO `block_assignment` VALUES (3, 0);
INSERT INTO `block_assignment` VALUES (4, 0);
INSERT INTO `block_assignment` VALUES (5, 0);
INSERT INTO `block_assignment` VALUES (5, 27);
INSERT INTO `block_assignment` VALUES (10, 0);
INSERT INTO `block_assignment` VALUES (11, 0);
INSERT INTO `block_assignment` VALUES (12, 0);
INSERT INTO `block_assignment` VALUES (17, 0);
INSERT INTO `block_assignment` VALUES (18, 0);
INSERT INTO `block_assignment` VALUES (19, 0);
INSERT INTO `block_assignment` VALUES (20, 0);


INSERT INTO `block_role` VALUES (1, -2);
INSERT INTO `block_role` VALUES (2, -2);
INSERT INTO `block_role` VALUES (3, -2);
INSERT INTO `block_role` VALUES (4, -2);
INSERT INTO `block_role` VALUES (5, 1);
INSERT INTO `block_role` VALUES (10, -2);
INSERT INTO `block_role` VALUES (11, -2);
INSERT INTO `block_role` VALUES (12, -2);
INSERT INTO `block_role` VALUES (17, 1);
INSERT INTO `block_role` VALUES (18, -2);
INSERT INTO `block_role` VALUES (19, -2);
INSERT INTO `block_role` VALUES (20, -2);
INSERT INTO `block_role` VALUES (17, 0);