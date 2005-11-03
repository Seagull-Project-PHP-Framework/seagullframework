#
# Dumping data for table `block`
#


INSERT INTO block VALUES (1, 'SiteNews', 'Site News', '', '', 2, 1, 1, NULL);
INSERT INTO block VALUES (2, 'DirectoryNav', 'Navigation', '', 'navWidget', 1, 1, 1, NULL);
INSERT INTO block VALUES (3, 'SampleBlock1', 'SourceForge Site', '', '', 3, 1, 1, NULL);
INSERT INTO block VALUES (4, 'SampleBlock2', 'Syndication', '', '', 4, 1, 1, NULL);
INSERT INTO block VALUES (5, 'CategoryNav', 'Categories', '', 'navWidget', 1, 1, 1, NULL);
INSERT INTO block VALUES (10, 'SampleRightBlock1', 'Sample Right Block', '', '', 1, 0, 1, NULL);
INSERT INTO block VALUES (11, 'CalendarBlock', 'Calendar', '', '', 2, 0, 0, NULL);
INSERT INTO block VALUES (12, 'LoginBlock', 'Login', '', '', 3, 0, 0, NULL);
INSERT INTO block VALUES (13, 'ShopNav', 'Products', '', 'sgl-dropdown', 1, 1, 0, NULL);
INSERT INTO block VALUES (14, 'ShoppingCart', 'Cart', '', '', 1, 0, 0, NULL);
INSERT INTO block VALUES (15, 'RndProducts', 'Promotions', '', 'rndProducts', 5, 0, 0, NULL);
INSERT INTO block VALUES (16, 'ShopSearch', 'Search', '', '', 7, 1, 0, NULL);
INSERT INTO block VALUES (17, 'NewsletterBlock', 'Newsletter', '', '', 5, 0, 0, NULL);
INSERT INTO block VALUES (18, 'Exchange', 'Exchange', '', '', 9, 1, 0, NULL);


#
# Dumping data for table `block_assignment`
#

INSERT INTO block_assignment VALUES (1, 0);
INSERT INTO block_assignment VALUES (3, 0);
INSERT INTO block_assignment VALUES (4, 0);
INSERT INTO block_assignment VALUES (5, 2);
INSERT INTO block_assignment VALUES (5, 17);
INSERT INTO block_assignment VALUES (5, 18);
INSERT INTO block_assignment VALUES (5, 19);
INSERT INTO block_assignment VALUES (10, 0);
INSERT INTO block_assignment VALUES (11, 0);
INSERT INTO block_assignment VALUES (12, 0);
INSERT INTO block_assignment VALUES (13, 22);
INSERT INTO block_assignment VALUES (13, 24);
INSERT INTO block_assignment VALUES (16, 0);
INSERT INTO block_assignment VALUES (17, 0);
INSERT INTO block_assignment VALUES (18, 0);

INSERT INTO block_role VALUES (1, -2);
INSERT INTO block_role VALUES (2, -2);
INSERT INTO block_role VALUES (3, -2);
INSERT INTO block_role VALUES (4, -2);
INSERT INTO block_role VALUES (5, -2);
INSERT INTO block_role VALUES (10, -2);
INSERT INTO block_role VALUES (11, -2);
INSERT INTO block_role VALUES (12, -2);
INSERT INTO block_role VALUES (13, -2);
INSERT INTO block_role VALUES (14, -2);
INSERT INTO block_role VALUES (15, -2);
INSERT INTO block_role VALUES (16, -2);
INSERT INTO block_role VALUES (17, -2);
INSERT INTO block_role VALUES (18, -2);