-- ==============================================================
--  DBMS name:      MaxDB 7.3
--  Created on:     2004-04-13 23:45:57
-- ==============================================================

-- Constraints for /block


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--

ALTER TABLE block_assignment ADD FOREIGN KEY (block_id) REFERENCES block;
ALTER TABLE block_assignment ADD FOREIGN KEY (section_id) REFERENCES section;


COMMIT;







