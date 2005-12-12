-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL 
-- ==========================================================================

-- Data dump for /modules/block


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--

--
-- Dumping data for table block
--

INSERT INTO block VALUES (1,'SiteNews','Site News','','',2,'Left',1);
INSERT INTO block VALUES (2,'DirectoryNav','Navigation','','navWidget',1,'Left',1);
INSERT INTO block VALUES (3,'SampleBlock1','SourceForge Site','','',3,'Left',1);
INSERT INTO block VALUES (4,'SampleBlock2','Syndication','','',4,'Left',1);
INSERT INTO block VALUES (5,'CategoryNav','Categories','','navWidget',1,'Left',1);
INSERT INTO block VALUES (10,'SampleRightBlock1','Sample Right Block','','',1,'Right',1);
INSERT INTO block VALUES (11,'CalendarBlock','Calendar','sgl-blocks-left-item-title','sgl-blocks-left-item-body',2,'Right',0);
INSERT INTO block VALUES (12,'LoginBlock','Login','sgl-blocks-right-item-title','sgl-blocks-right-item-body',3,'Right',0);

--
-- Dumping data for table block_assignment
--

INSERT INTO block_assignment VALUES (1,0);
INSERT INTO block_assignment VALUES (5,2);
INSERT INTO block_assignment VALUES (5,18);
INSERT INTO block_assignment VALUES (5,19);
INSERT INTO block_assignment VALUES (3,0);
INSERT INTO block_assignment VALUES (4,0);
INSERT INTO block_assignment VALUES (5,17);
INSERT INTO block_assignment VALUES (10,0);
INSERT INTO block_assignment VALUES (11,0);
INSERT INTO block_assignment VALUES (12,0);


COMMIT;
