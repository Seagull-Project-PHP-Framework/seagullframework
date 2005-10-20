-- ==============================================================
--  DBMS name:      MaxDB 7.3
--  Created on:     2004-04-13 23:45:57
-- ==============================================================

-- Constraints for /publisher


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--

ALTER TABLE document ADD FOREIGN KEY (category_id) REFERENCES category;
ALTER TABLE document ADD FOREIGN KEY (document_type_id) REFERENCES document_type;

COMMIT;







