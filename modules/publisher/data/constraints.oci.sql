-- ==============================================================
--  create foreign keys
-- ==============================================================
alter table document add constraint FK_document_document_type foreign key (document_type_id)  references document_type (document_type_id);
alter table document add constraint FK_category_document foreign key (category_id) references category (category_id);
