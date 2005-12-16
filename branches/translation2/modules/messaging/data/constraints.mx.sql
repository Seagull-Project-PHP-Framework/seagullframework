-- ==============================================================
--  DBMS name:      MaxDB 7.3
--  Created on:     2004-04-13 23:45:57
-- ==============================================================

-- Constraints for /messaging


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--

ALTER TABLE instant_message ADD FOREIGN KEY (user_id_to) REFERENCES usr;
ALTER TABLE instant_message ADD FOREIGN KEY (user_id_from) REFERENCES usr;

COMMIT;







