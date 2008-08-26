/*==============================================================*/
/* Table: user_passwd_hash                                      */
/*==============================================================*/
ALTER TABLE `user_passwd_hash`
    ADD FOREIGN KEY (`usr_id`) REFERENCES `usr` (`usr_id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

/*==============================================================*/
/* Table: usr                                                   */
/*==============================================================*/
ALTER TABLE `usr`
    ADD FOREIGN KEY (`address_id`) REFERENCES `address` (`address_id`)
    ON DELETE SET NULL ON UPDATE SET NULL;