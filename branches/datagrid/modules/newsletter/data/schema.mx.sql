-- ==========================================================================
-- Seagull PHP Framework: Default Data for PostgreSQL
-- ==========================================================================

-- Schema for /modules/faq


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
-- ==============================================================
--  Table: newsletter
-- ==============================================================

CREATE TABLE newsletter (
  newsletter_id  INTEGER       NOT NULL default '0',
  title          VARCHAR(32)   NOT NULL default '',
  name           VARCHAR(128)  NOT NULL default '',
  email          VARCHAR(128)  NOT NULL default '',
  status         INTEGER       NOT NULL default '0',
  action_request VARCHAR(32)   NOT NULL default '',
  action_key     VARCHAR(64)   NOT NULL default '',
  date_created   TIMESTAMP     NOT NULL default '1970-01-01 00:00:00',
  last_updated   TIMESTAMP     NOT NULL default '1970-01-01 00:00:00'
) ;

ALTER TABLE newsletter ADD PRIMARY KEY (newsletter_id);

COMMIT;
