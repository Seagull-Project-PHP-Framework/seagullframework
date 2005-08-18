#
# Dumping data for table `block`
#


INSERT INTO block VALUES (1, 'SiteNews', 'Site News', '', '', 2, 1, 1);
INSERT INTO block VALUES (2, 'DirectoryNav', 'Navigation', '', 'navWidget', 1, 1, 1);
INSERT INTO block VALUES (3, 'SampleBlock1', 'Sample Block 1', '', '', 3, 1, 1);
INSERT INTO block VALUES (4, 'SampleBlock2', 'Sample Block 2', '', '', 4, 1, 1);
INSERT INTO block VALUES (5, 'CategoryNav', 'Categories', '', 'navWidget', 1, 1, 1);
INSERT INTO block VALUES (10, 'SampleRightBlock1', 'Sample Right Block', '', '', 1, 0, 1);
INSERT INTO block VALUES (11, 'CalendarBlock', 'Calendar', '', '', 2, 0, 0);
INSERT INTO block VALUES (12, 'LoginBlock', 'Login', '', '', 3, 0, 0);
INSERT INTO block VALUES (13, 'ShopNav', 'Products', '', 'sgl-dropdown', 1, 1, 0);
INSERT INTO block VALUES (14, 'ShoppingCart', 'Cart', '', '', 1, 0, 0);
INSERT INTO block VALUES (15, 'RndProducts', 'Promotions', '', 'rndProducts', 5, 0, 0);
INSERT INTO block VALUES (16, 'ShopSearch', 'Search', '', '', 7, 1, 0);
INSERT INTO block VALUES (17, 'NewsletterBlock', 'Newsletter', '', '', 5, 0, 0);
INSERT INTO block VALUES (18, 'Exchange', 'Exchange', '', '', 9, 1, 0);


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


