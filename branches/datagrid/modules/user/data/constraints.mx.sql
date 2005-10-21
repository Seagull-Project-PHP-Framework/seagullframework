-- ==============================================================
--  DBMS name:      MaxDB 7.3
--  Created on:     2004-04-13 23:45:57
-- ==============================================================

-- Constraints for /user


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
ALTER TABLE role_permission ADD FOREIGN KEY (role_id) REFERENCES role;
ALTER TABLE role_permission ADD FOREIGN KEY (permission_id) REFERENCES permission;

ALTER TABLE user_permission ADD FOREIGN KEY (permission_id) REFERENCES permission;
ALTER TABLE user_permission ADD FOREIGN KEY (usr_id) REFERENCES usr;

ALTER TABLE user_preference ADD FOREIGN KEY (usr_id) REFERENCES usr;
ALTER TABLE user_preference ADD FOREIGN KEY (preference_id) REFERENCES preference;

ALTER TABLE org_preference ADD FOREIGN KEY (organisation_id) REFERENCES organisation;
ALTER TABLE org_preference ADD FOREIGN KEY (preference_id) REFERENCES preference;

ALTER TABLE user_preference ADD FOREIGN KEY (preference_id) REFERENCES preference;
ALTER TABLE user_preference ADD FOREIGN KEY (usr_id) REFERENCES usr;

ALTER TABLE login ADD FOREIGN KEY (usr_id) REFERENCES usr;

COMMIT;







