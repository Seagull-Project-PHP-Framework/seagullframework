/*==============================================================*/
/* Table: email_queue                                           */
/*==============================================================*/
CREATE TABLE email_queue (
  email_queue_id integer NOT NULL,
  date_created timestamp NOT NULL,
  date_to_send timestamp NOT NULL,
  date_sent timestamp default NULL,
  mail_headers text NOT NULL,
  mail_recipient varchar(255) NOT NULL,
  mail_body text NOT NULL,
  mail_subject varchar(255) DEFAULT NULL,
  attempts smallint NOT NULL DEFAULT 0,
  usr_id integer DEFAULT NULL,
  group_id integer DEFAULT NULL,
  batch_id integer DEFAULT NULL,
  CONSTRAINT pk_email_queue PRIMARY KEY (email_queue_id)
);

CREATE INDEX idx_email_queue_date_to_send ON email_queue(date_to_send);
CREATE INDEX idx_email_queue_usr_id ON email_queue(usr_id);