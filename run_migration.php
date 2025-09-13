<?php
// Manual migration runner to fix location column issue

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Set up database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'hse_database',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    $pdo = $capsule->getConnection()->getPdo();

    echo "Connected to database successfully\n";

    // Check if location_id column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM reports LIKE 'location_id'");
    $locationIdExists = $stmt->fetch() !== false;

    // Check if location column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM reports LIKE 'location'");
    $locationExists = $stmt->fetch() !== false;

    echo "location_id exists: " . ($locationIdExists ? 'YES' : 'NO') . "\n";
    echo "location exists: " . ($locationExists ? 'YES' : 'NO') . "\n";

    if (!$locationIdExists) {
        echo "Adding location_id column...\n";
        $pdo->exec("ALTER TABLE reports ADD COLUMN location_id BIGINT UNSIGNED NULL AFTER action_id");
        echo "location_id column added\n";
    }

    // Check if foreign key exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                        WHERE CONSTRAINT_SCHEMA = 'hse_database'
                        AND TABLE_NAME = 'reports'
                        AND CONSTRAINT_NAME = 'reports_location_id_foreign'");
    $fkExists = $stmt->fetch()['count'] > 0;

    if (!$fkExists) {
        echo "Adding foreign key constraint...\n";
        $pdo->exec("ALTER TABLE reports ADD CONSTRAINT reports_location_id_foreign
                   FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL");
        echo "Foreign key added\n";
    }

    if ($locationExists) {
        echo "Dropping old location column...\n";
        $pdo->exec("ALTER TABLE reports DROP COLUMN location");
        echo "Old location column dropped\n";
    }

    // Add migration record
    $stmt = $pdo->prepare("INSERT IGNORE INTO migrations (migration, batch)
                          VALUES (?, (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT * FROM migrations) m))");
    $stmt->execute(['2025_09_13_000002_update_reports_table_change_location_to_location_id']);

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}