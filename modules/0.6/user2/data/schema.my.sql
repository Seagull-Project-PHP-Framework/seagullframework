/*==============================================================*/
/* Table: user_passwd_hash                                      */
/*==============================================================*/
CREATE TABLE `user_passwd_hash` (
    `user_passwd_hash_id`     int(11)             NOT NULL,
    `usr_id`                  int(11)             NOT NULL,
    `hash`                    varchar(32)         NOT NULL,
    `date_created`            datetime            NOT NULL,

    PRIMARY KEY (`user_passwd_hash_id`),
    KEY (`usr_id`)
) ENGINE=InnoDB;