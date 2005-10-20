-- ==============================================================
--  DBMS name:      MaxDB 7.3
--  Created on:     2004-04-13 23:45:57
-- ==============================================================

-- Constraints for /etc


-- Begin a transaction-- This is not really necessary, but is very useful in developing phase. ;-)
--

ALTER TABLE item ADD FOREIGN KEY (item_type_id) REFERENCES item_type;
ALTER TABLE item_addition ADD FOREIGN KEY (item_id) REFERENCES item;
ALTER TABLE item_addition ADD FOREIGN KEY (item_type_mapping_id) REFERENCES item_type_mapping;
ALTER TABLE item_type_mapping ADD FOREIGN KEY (item_type_id) REFERENCES item_type;

COMMIT;

