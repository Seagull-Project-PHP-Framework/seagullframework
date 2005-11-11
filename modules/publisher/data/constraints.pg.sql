-- ==============================================================
--  DBMS name:      PostgreSQL 7.3                               
--  Created on:     2004-04-13 23:45:57                          
-- ==============================================================

-- Constraints for /publisher


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
BEGIN;

alter table document add constraint FK_category_document foreign key (category_id)
      references category (category_id) on delete restrict on update restrict;

alter table document add constraint FK_document_document_type foreign key (document_type_id)
      references document_type (document_type_id) on delete restrict on update restrict;

COMMIT;







