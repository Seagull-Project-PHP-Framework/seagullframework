-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL 
-- ==========================================================================

-- Data dump for /modules/messaging


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

--
-- Dumping data for table category
--
INSERT INTO category (category_id, label, perms, parent_id, root_id, left_id, right_id, order_id, level_id) VALUES (0, 'HiddenRoot', '', 0, 0, 0, 0, 0, 0);
INSERT INTO category (category_id, label, perms, parent_id, root_id, left_id, right_id, order_id, level_id) VALUES (1, 'PublisherRoot', '', 0, 1, 1, 4, 1, 1);
INSERT INTO category (category_id, label, perms, parent_id, root_id, left_id, right_id, order_id, level_id) VALUES (2, 'example', '', 1, 1, 2, 3, 1, 2);
INSERT INTO category (category_id, label, perms, parent_id, root_id, left_id, right_id, order_id, level_id) VALUES (3, 'OtherRoot', '', 0, 3, 1, 2, 1, 1);


--
-- Dumping data for table section
--

INSERT INTO section VALUES (0,'none','','0',0,0,1,2,0,1,0,0,0,0);
INSERT INTO section VALUES (1,'Home','','1,2,0',0,1,1,2,1,1,1,0,1,0);
INSERT INTO section VALUES (2,'Articles','publisher/articleview','1,2,0',0,2,1,2,6,1,1,0,0,0);
INSERT INTO section VALUES (3,'FAQ','faq/faq','1,2,0',0,3,1,2,7,1,1,0,0,0);
INSERT INTO section VALUES (4,'My Account','user/account','1,2',0,4,1,2,9,1,1,0,0,0);
INSERT INTO section VALUES (5,'Messages','messaging/imessage','1,2',0,5,1,2,5,1,1,0,0,0);
INSERT INTO section VALUES (6,'Sample','publisher/articleview/frmArticleID/1','0',0,6,1,2,4,1,1,1,0,0);
INSERT INTO section VALUES (7,'Contact Us','contactus/contactus','1,2,0',0,7,1,8,2,1,1,0,9,0);
INSERT INTO section VALUES (8,'Register Now','user/register','0',0,8,1,2,8,1,1,0,0,0);
INSERT INTO section VALUES (9,'Publisher','publisher/article','2',0,9,1,2,3,1,1,0,0,0);
INSERT INTO section VALUES (11,'testSection','faq/faq','0',15,7,5,6,1,3,1,0,0,0);
INSERT INTO section VALUES (12,'Modules','default/module','1',0,12,1,4,11,1,1,0,0,0);
INSERT INTO section VALUES (13,'Configuration','default/config','1',0,13,1,2,10,1,1,0,0,0);
INSERT INTO section VALUES (14,'Get a quote','contactus/contactus/action/list/enquiry_type/Get a quote','1,2,0',7,7,2,3,1,2,1,0,0,0);
INSERT INTO section VALUES (15,'Hosting info','contactus/contactus/action/list/enquiry_type/Hosting info','1,2,0',7,7,4,7,2,2,1,0,0,0);
INSERT INTO section VALUES (16,'Manage','default/module/action/list','1',12,12,2,3,1,2,1,0,0,0);
INSERT INTO section VALUES (17,'PubCategories','navigation/category','1',0,17,1,2,12,1,0,0,0,0);
INSERT INTO section VALUES (18,'PubDocuments','publisher/document','1',0,18,1,2,13,1,0,0,0,0);
INSERT INTO section VALUES (19,'PubArticles','publisher/article','1',0,19,1,2,14,1,0,0,0,0); 

COMMIT;
