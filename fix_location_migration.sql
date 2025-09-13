USE hse_database;

-- Add location_id column if it doesn't exist
ALTER TABLE reports
ADD COLUMN IF NOT EXISTS location_id BIGINT UNSIGNED NULL AFTER action_id;

-- Add foreign key constraint if it doesn't exist
SET @constraint_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = 'hse_database'
    AND TABLE_NAME = 'reports'
    AND CONSTRAINT_NAME = 'reports_location_id_foreign');

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE reports ADD CONSTRAINT reports_location_id_foreign FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL',
    'SELECT "Foreign key already exists" as message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop the old location column if it exists
ALTER TABLE reports DROP COLUMN IF EXISTS location;

-- Insert migration record if it doesn't exist
INSERT IGNORE INTO migrations (migration, batch)
VALUES ('2025_09_13_000002_update_reports_table_change_location_to_location_id',
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations m));

SELECT "Migration completed successfully" as result;