-- ==========================================================================
-- Seagull PHP Framework: Default Data for MaxDB
-- ==========================================================================

-- Schema for /modules/navigation


-- Begin a transaction
-- This is not really necessary, but is very useful in developing phase. ;-)
--
-- ==============================================================
--  Foreign Keys
-- ==============================================================
ALTER TABLE category ADD FOREIGN KEY (parent_id) REFERENCES category;

COMMIT;