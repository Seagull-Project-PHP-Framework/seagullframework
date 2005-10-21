alter table instant_message add constraint FK_usr_instant_from foreign key (user_id_to)
      references usr (usr_id) on delete restrict on update restrict;

alter table instant_message add constraint FK_ust_instant_to foreign key (user_id_from)
      references usr (usr_id) on delete restrict on update restrict;