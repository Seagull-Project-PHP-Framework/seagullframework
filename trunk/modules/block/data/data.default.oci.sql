--
-- Dumping data for table block
--

INSERT INTO block VALUES (1, 'SiteNews', 'Site News', '', '', 2, 1, 1, NULL);
INSERT INTO block VALUES (2, 'DirectoryNav', 'Navigation', '', 'navWidget', 1, 1, 1, NULL);
INSERT INTO block VALUES (3, 'SampleBlock1', 'SourceForge Site', '', '', 3, 1, 1, NULL);
INSERT INTO block VALUES (4, 'SampleBlock2', 'Syndication', '', '', 4, 1, 1, NULL);
INSERT INTO block VALUES (5, 'CategoryNav', 'Categories', '', 'navWidget', 1, 1, 1, NULL);
INSERT INTO block VALUES (10, 'SampleRightBlock1', 'Sample Right Block', '', '', 1, 0, 1, NULL);
INSERT INTO block VALUES (11, 'CalendarBlock', 'Calendar', 'blockHeader', 'blockContent', 2, 0, 0, NULL);
INSERT INTO block VALUES (12, 'LoginBlock', 'Login', 'blockHeader', 'blockContent', 3, 0, 0, NULL);
INSERT INTO block VALUES (13, 'ShopNav', 'Products', '', 'sgl-dropdown', 1, 1, 0, NULL);
INSERT INTO block VALUES (14, 'ShoppingCart', 'Cart', 'blockHeader', 'blockContent', 1, 0, 0, NULL);
INSERT INTO block VALUES (15, 'RndProducts', 'Promotions', 'blockHeader', 'rndProducts', 5, 0, 0, NULL);
INSERT INTO block VALUES (16, 'ShopSearch', 'Search', 'blockHeader', 'blockContent', 7, 1, 0, NULL);
INSERT INTO block VALUES (17, 'NewsletterBlock', 'Newsletter', 'blockHeader', 'blockContent', 5, 0, 0, NULL);
INSERT INTO block VALUES (18, 'Exchange', 'Exchange', 'blockHeader', 'blockContent', 9, 1, 0, NULL);

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
INSERT INTO block_assignment VALUES (13, 22);
INSERT INTO block_assignment VALUES (13, 24);
INSERT INTO block_assignment VALUES (16, 0);
INSERT INTO block_assignment VALUES (17, 0);
INSERT INTO block_assignment VALUES (18, 0);

--
-- Creating sequences
-- sequence must start on the first free record id
--

CREATE SEQUENCE block_seq START WITH 19;
CREATE SEQUENCE block_assignment_seq START WITH 19;
