-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Data dump for /modules/block

BEGIN;

--
-- Dumping data for table block
--

INSERT INTO block VALUES (1, 'SiteNews', 'Site News', '', '', 2, 'Left', 1, NULL);
INSERT INTO block VALUES (3, 'SampleBlock1', 'SourceForge Site', '', '', 3, 'Left', 1, NULL);
INSERT INTO block VALUES (4, 'SampleBlock2', 'Syndication', '', '', 4, 'Left', 1, NULL);
INSERT INTO block VALUES (5, 'CategoryNav', 'Categories', '', 'navWidget', 1, 'Left', 1, NULL);
INSERT INTO block VALUES (10, 'SampleRightBlock1', 'Sample Right Block', '', '', 1, 'Right', 1, NULL);
INSERT INTO block VALUES (11, 'CalendarBlock', 'Calendar', '', '', 2, 'Right', 0, NULL);
INSERT INTO block VALUES (12, 'LoginBlock', 'Login', '', '', 3, 'Right', 0, NULL);
INSERT INTO block VALUES (13, 'ShopNav', 'Products', '', 'sgl-dropdown', 1, 'Left', 0, NULL);
INSERT INTO block VALUES (14, 'ShoppingCart', 'Cart', '', '', 1, 'Right', 0, NULL);
INSERT INTO block VALUES (15, 'RndProducts', 'Promotions', '', 'rndProducts', 5, 'Right', 0, NULL);
INSERT INTO block VALUES (16, 'ShopSearch', 'Search', '', '', 7, 'Left', 0, NULL);
INSERT INTO block VALUES (17, 'NewsletterBlock', 'Newsletter', '', '', 5, 'Right', 0, NULL);
INSERT INTO block VALUES (18, 'Exchange', 'Exchange', '', '', 9, 'Left', 0, NULL);

--
-- Dumping data for table block_assignment
--

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
-- INSERT INTO block_assignment VALUES (13, 22);
-- INSERT INTO block_assignment VALUES (13, 24);
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

COMMIT;
