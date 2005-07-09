#
# Dumping data for table `category`
#

INSERT INTO category VALUES (1,'PublisherRoot',NULL,0,1,1,4,1,1);
INSERT INTO category VALUES (2,'example',NULL,1,1,2,3,1,2);
INSERT INTO category VALUES (3,'OtherRoot',NULL,0,3,1,2,1,1);
INSERT INTO category VALUES (4,'Shop',NULL,0,4,1,16,2,1);
INSERT INTO category VALUES (6,'Printers',NULL,4,4,8,9,2,2);
INSERT INTO category VALUES (5,'Monitors',NULL,4,4,2,7,1,2);
INSERT INTO category VALUES (13,'CRT',NULL,5,4,3,4,1,3);
INSERT INTO category VALUES (7,'Laptop Computers',NULL,4,4,10,15,3,2);
INSERT INTO category VALUES (9,'Notebook',NULL,7,4,11,12,1,3);
INSERT INTO category VALUES (11,'Tablet PC',NULL,7,4,13,14,2,3);
INSERT INTO category VALUES (15,'LCD',NULL,5,4,5,6,2,3);
#
# Dumping data for table `section`
#

INSERT INTO section VALUES (0, 'none', '', '0', 0, 0, 1, 2, 0, 1, 0, 0);
INSERT INTO section VALUES (1, 'Home', '', '1,2,0', 0, 1, 1, 2, 1, 1, 1, 0);
INSERT INTO section VALUES (2, 'Articles', 'publisher/articleview', '1,2,0', 0, 2, 1, 2, 6, 1, 1, 0);
INSERT INTO section VALUES (3, 'FAQ', 'faq/faq', '1,2,0', 0, 3, 1, 2, 7, 1, 1, 0);
INSERT INTO section VALUES (4, 'My Account', 'user/account', '1,2', 0, 4, 1, 2, 9, 1, 1, 0);
INSERT INTO section VALUES (5, 'Messages', 'messaging/imessage', '1,2', 0, 5, 1, 2, 5, 1, 1, 0);
INSERT INTO section VALUES (6, 'Sample', 'publisher/articleview/frmArticleID/1', '0', 0, 6, 1, 2, 4, 1, 1, 1);
INSERT INTO section VALUES (7, 'Contact Us', 'contactus/contactus', '1,2,0', 0, 7, 1, 8, 2, 1, 1, 0);
INSERT INTO section VALUES (8, 'Register Now', 'user/register', '0', 0, 8, 1, 2, 8, 1, 1, 0);
INSERT INTO section VALUES (9, 'Publisher', 'publisher/article', '2', 0, 9, 1, 2, 3, 1, 1, 0);
INSERT INTO section VALUES (11, 'testSection', 'faq/faq', '0', 15, 7, 5, 6, 1, 3, 1, 0);
INSERT INTO section VALUES (12, 'Modules', 'default/module', '1', 0, 12, 1, 4, 11, 1, 1, 0);
INSERT INTO section VALUES (13, 'Configuration', 'default/config', '1', 0, 13, 1, 2, 10, 1, 1, 0);
INSERT INTO section VALUES (14, 'Get a quote', 'contactus/contactus/action/list/enquiry_type/Get a quote', '1,2,0', 7, 7, 2, 3, 1, 2, 1, 0);
INSERT INTO section VALUES (15, 'Hosting info', 'contactus/contactus/action/list/enquiry_type/Hosting info', '1,2,0', 7, 7, 4, 7, 2, 2, 1, 0);
INSERT INTO section VALUES (16, 'Manage', 'default/module/action/list', '1', 12, 12, 2, 3, 1, 2, 1, 0);
INSERT INTO section VALUES (17, 'PubCategories', 'navigation/category', '1', 0, 17, 1, 2, 12, 1, 0, 0);
INSERT INTO section VALUES (18, 'PubDocuments', 'publisher/document', '1', 0, 18, 1, 2, 13, 1, 0, 0);
INSERT INTO section VALUES (19, 'PubArticles', 'publisher/article', '1', 0, 19, 1, 2, 14, 1, 0, 0);
INSERT INTO section VALUES (22, 'Shop', 'shop/priceadmin', '1,2,0', 0, 22, 1, 4, 15, 1, 0, 0);
INSERT INTO section VALUES (24, 'ShopAdmin', 'shop/shopadmin', '1', 22, 22, 2, 3, 1, 2, 0, 0);
INSERT INTO section VALUES (25, 'Price', 'shop/price', '1,2,0', 0, 25, 1, 4, 16, 1, 0, 0);

