-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL
-- ==========================================================================

-- Data dump for /modules/publisher


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--

--
-- Dumping data for table document_type
--

INSERT INTO document_type VALUES (1, 'MS Word');
INSERT INTO document_type VALUES (2, 'MS Excel');
INSERT INTO document_type VALUES (3, 'MS Powerpoint');
INSERT INTO document_type VALUES (4, 'URL');
INSERT INTO document_type VALUES (5, 'Image');
INSERT INTO document_type VALUES (6, 'PDF');
INSERT INTO document_type VALUES (7, 'unknown');

COMMIT;