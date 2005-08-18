-- ==============================================================
--  DBMS name:      PostgreSQL 7.3                               
--  Created on:     2004-04-13 23:45:57                          
-- ==============================================================

-- Constraints for /messaging


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

alter table instant_message add constraint FK_usr_instant_from foreign key (user_id_to)
      references usr (usr_id) on delete restrict on update restrict;

alter table instant_message add constraint FK_ust_instant_to foreign key (user_id_from)
      references usr (usr_id) on delete restrict on update restrict;

COMMIT;







