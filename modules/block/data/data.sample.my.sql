#
# Dumping data for table `block`
#
INSERT INTO `block` VALUES (1, 'SiteNews', 'Site News', '', '', 2, 'Left', 1, NULL, 'N;');
INSERT INTO `block` VALUES (3, 'SampleBlock1', 'SourceForge Site', '', '', 4, 'Left', 0, NULL, 'N;');
INSERT INTO `block` VALUES (4, 'SampleBlock2', 'Syndication', '', '', 3, 'Left', 0, NULL, 'N;');
INSERT INTO `block` VALUES (5, 'CategoryNav', 'Categories', '', 'navWidget', 1, 'AdminCategory', 1, NULL, 'N;');
INSERT INTO `block` VALUES (10, 'SampleRightBlock1', 'Sample Right Block', '', '', 5, 'Right', 1, NULL, NULL);
INSERT INTO `block` VALUES (11, 'CalendarBlock', 'Calendar', '', '', 2, 'Left', 0, NULL, 'N;');
INSERT INTO `block` VALUES (12, 'LoginBlock', 'Login', '', '', 1, 'Right', 1, NULL, 'N;');
INSERT INTO `block` VALUES (17, 'RecentHtmlArticles2', 'Recent articles', '', '', 3, 'Right', 1, NULL, 'N;');
INSERT INTO `block` VALUES (18, 'Navigation', 'User menu', '', '', 1, 'UserNav', 1, NULL, 'a:6:{s:15:"startParentNode";s:1:"1";s:10:"startLevel";s:1:"0";s:14:"levelsToRender";s:1:"0";s:9:"collapsed";s:1:"1";s:10:"showAlways";s:1:"1";s:11:"breadcrumbs";s:1:"0";}');
INSERT INTO `block` VALUES (19, 'Article', 'Articles statiques', '', '', 4, 'Right', 0, NULL, 'a:2:{s:9:"articleId";s:1:"2";s:8:"template";s:33:"articleViewStaticHtmlArticle.html";}');
INSERT INTO `block` VALUES (20, 'Navigation', 'Admin menu', '', '', 1, 'AdminNav', 1, NULL, 'a:6:{s:15:"startParentNode";s:2:"22";s:10:"startLevel";s:1:"0";s:14:"levelsToRender";s:1:"0";s:9:"collapsed";s:1:"0";s:10:"showAlways";s:1:"1";s:11:"breadcrumbs";s:1:"0";}');


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