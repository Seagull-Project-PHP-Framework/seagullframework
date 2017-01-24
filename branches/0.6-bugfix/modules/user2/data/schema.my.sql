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

/*==============================================================*/
/* Table: address                                               */
/*==============================================================*/
CREATE TABLE `address` (
    `address_id`              int(11)             NOT NULL,
    `address1`                varchar(128)                       DEFAULT NULL,
    `address2`                varchar(128)                       DEFAULT NULL,
    `city`                    varchar(128)                       DEFAULT NULL,
    `state`                   varchar(128)                       DEFAULT NULL,
    `post_code`               varchar(128)                       DEFAULT NULL,
    `country`                 varchar(128)                       DEFAULT NULL,

    PRIMARY KEY (`address_id`)
) ENGINE=InnoDB;

/*==============================================================*/
/* Table: address                                               */
/*==============================================================*/
CREATE TABLE `user-address` (
    `usr_id`                  int(11)             NOT NULL,
    `address_id`              int(11)             NOT NULL,
    `address_type`            varchar(32)                        DEFAULT NULL,

    PRIMARY KEY (`usr_id`, `address_id`, `address_type`)
) ENGINE=InnoDB;