-- Last edited: Pierpaolo Toniolo 26-07-2005
-- Constraints for /publisher

BEGIN;

alter table document add constraint FK_category_document foreign key (category_id)
      references category (category_id) on delete restrict on update restrict;

alter table document add constraint FK_document_document_type foreign key (document_type_id)
      references document_type (document_type_id) on delete restrict on update restrict;

alter table item add constraint FK_item_item_type foreign key (item_type_id)
      references item_type (item_type_id) on delete restrict on update restrict;

alter table item_addition add constraint FK_item_item_addition foreign key (item_id)
      references item (item_id) on delete restrict on update restrict;

alter table item_addition add constraint FK_item_type_mapping_item_addition foreign key (item_type_mapping_id)
      references item_type_mapping (item_type_mapping_id) on delete restrict on update restrict;

alter table item_type_mapping add constraint FK_item_type_item_type_mapping foreign key (item_type_id)
      references item_type (item_type_id) on delete restrict on update restrict;

ALTER TABLE category ADD CONSTRAINT FK_parent FOREIGN KEY (parent_id)
      REFERENCES category (category_id) ON DELETE RESTRICT ON UPDATE RESTRICT;

COMMIT;







