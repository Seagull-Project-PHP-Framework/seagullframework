-- ==============================================================
--  create foreign keys
-- ==============================================================
alter table instant_message add constraint FK_usr_instant_from foreign key (user_id_to) references usr (usr_id);
alter table instant_message add constraint FK_ust_instant_to foreign key (user_id_from) references usr (usr_id);
alter table contact add foreign key (usr_id) references usr (usr_id) on delete cascade;
alter table contact add foreign key (originator_id) references usr (usr_id) on delete set null;
