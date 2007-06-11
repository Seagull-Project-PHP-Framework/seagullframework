-- ==============================================================
--  DBMS name:      Oracle 9.x                              
--  Created on:     2005-05-31 12:15:00

-- ==============================================================
--  Table: newsletter                                            
-- ==============================================================

CREATE TABLE newsletter (
  newsletter_id NUMBER(10) NOT NULL,
  list VARCHAR(32) NULL,
  name VARCHAR(128) NULL,
  email VARCHAR(128) NULL,
  status NUMBER(1) DEFAULT 0 NOT NULL,
  action_request VARCHAR(32) NULL,
  action_key VARCHAR(64) NULL,
  date_created DATE DEFAULT SYSDATE NOT NULL,
  last_updated DATE DEFAULT SYSDATE NOT NULL,
  constraint PK_NEWSLETTER PRIMARY KEY (newsletter_id)
); 

create sequence newsletter_seq;

