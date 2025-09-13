-- MANUAL FIX FOR LOCATION FIELD ISSUE
-- Run these commands directly in your MySQL database (phpMyAdmin, MySQL Workbench, etc.)

USE hse_database;

-- 1. Add location_id column if it doesn't exist
ALTER TABLE reports
ADD COLUMN location_id BIGINT UNSIGNED NULL AFTER action_id;

-- 2. Add foreign key constraint
ALTER TABLE reports
ADD CONSTRAINT reports_location_id_foreign
FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL;

-- 3. Drop the old location column
ALTER TABLE reports DROP COLUMN location;

-- 4. Record this migration as completed
INSERT INTO migrations (migration, batch)
VALUES ('2025_09_13_000002_update_reports_table_change_location_to_location_id',
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT * FROM migrations) m));

-- Verify the changes
DESCRIBE reports;