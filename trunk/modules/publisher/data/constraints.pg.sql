-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Constraints for /publisher

BEGIN;

alter table document add constraint FK_category_document foreign key (category_id)
      references category (category_id) on delete restrict on update restrict;

alter table document add constraint FK_document_document_type foreign key (document_type_id)
      references document_type (document_type_id) on delete restrict on update restrict;

COMMIT;







