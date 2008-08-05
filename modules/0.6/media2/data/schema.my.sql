/*==============================================================*/
/* Table: media                                                 */
/*==============================================================*/
CREATE TABLE `media` (
    `media_id`                 int(11)             NOT NULL,
    `media_type_id`            int(11)             NOT NULL,
    `fk_id`                    int(11)             NOT NULL,
    `name`                     varchar(128)        NOT NULL,
    `description`              text                               DEFAULT NULL,
    `item_order`               smallint                           DEFAULT 0,
    `file_name`                varchar(255)        NOT NULL,
    `file_size`                int(11)             NOT NULL,
    `mime_type`                varchar(32)         NOT NULL,
    `date_created`             datetime            NOT NULL,
    `last_updated`             datetime            NOT NULL,
    `created_by`               int(11)             NOT NULL,
    `updated_by`               int(11)             NOT NULL,

    PRIMARY KEY (`media_id`),
    KEY (`fk_id`)
);

/*==============================================================*/
/* Table: media_type                                            */
/*==============================================================*/
CREATE TABLE `media_type` (
    `media_type_id`            int(11)             NOT NULL,
    `name`                     varchar(128)        NOT NULL,
    `description`              text                               DEFAULT NULL,

    PRIMARY KEY (`media_type_id`)
);

/*==============================================================*/
/* Table: media_mime                                            */
/*==============================================================*/
CREATE TABLE `media_mime` (
    `media_mime_id`            int(11)             NOT NULL,
    `name`                     varchar(128)        NOT NULL,
    `extension`                varchar(128)        NOT NULL,
    `content_type`             varchar(128)        NOT NULL,
    `idents`                   varchar(128)        NOT NULL,

    PRIMARY KEY (`media_mime_id`)
);

/*==============================================================*/
/* Table: media_type-mime                                       */
/*==============================================================*/
CREATE TABLE `media_type-mime` (
    `media_type_id`            int(11)             NOT NULL,
    `media_mime_id`            int(11)             NOT NULL,

    PRIMARY KEY (`media_type_id`, `media_mime_id`)
);