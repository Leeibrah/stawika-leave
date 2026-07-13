<?php
namespace app\database;

use PDO;
use app\database\Database;
use app\database\TableBlueprint;

class Migration
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function createTable($tableName, callable $callback)
    {
        try {
            $blueprint = new TableBlueprint();
            $callback($blueprint);

            $columnsSql = array_map(
                fn($name, $definition) => "$name $definition",
                array_keys($blueprint->getColumns()),
                $blueprint->getColumns()
            );

            $sql = "CREATE TABLE $tableName (" . implode(', ', $columnsSql) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $this->db->getConnection()->exec($sql);

            foreach ($blueprint->getForeignKeys() as $key) {
                $this->addForeignKeyConstraint($tableName, $key);
            }
        } catch (\PDOException $e) {
            echo "\033[1;31mError creating table '$tableName': " . $e->getMessage() . "\033[0m\n";
        }
    }

    private function addForeignKeyConstraint($tableName, $key)
    {
        $columnName = $key['columnName'];
        $refTableName = $key['refTableName'];
        $refColumnName = $key['refColumnName'];

        // Generate constraint name
        $constraintName = $this->generateConstraintName($tableName, $columnName);

        try {
            // Add the foreign key constraint with ON DELETE CASCADE
            $sql = "ALTER TABLE $tableName 
                    ADD CONSTRAINT $constraintName 
                    FOREIGN KEY ($columnName) 
                    REFERENCES $refTableName($refColumnName) 
                    ON DELETE CASCADE";
            $this->db->getConnection()->exec($sql);
        } catch (\PDOException $e) {
            echo "\033[1;31mError adding foreign key constraint to '$tableName': " . $e->getMessage() . "\033[0m\n";
        }
    }

    public function dropTable($tableName)
    {
        echo "\033[1;33mDrop table operation is disabled in this environment. No tables were dropped.\033[0m\n";
    }

    public function tableExists($tableName)
    {
        $result = $this->db->getConnection()->query("SHOW TABLES LIKE '$tableName'");
        return $result->rowCount() > 0;
    }

    public function current_time()
    {
        date_default_timezone_set("Africa/Nairobi");
        return date("m/d/Y h:i:s a", time());
    }

    public function executeTable($tableName, callable $callback)
    {
        try {
            $startTime = microtime(true); // Start timing

            if ($this->tableExists($tableName)) {
                echo "\033[1;33mTable $tableName already exists. Migration skipped to avoid data loss.\033[0m\n\n";
            } else {
                $this->createTable($tableName, $callback);
                $duration = round(microtime(true) - $startTime); // Calculate duration
                echo "\033[1;32mMigration for table '$tableName' successful! -----> {$duration}s\033[0m\n\n\n"; // Green for success, with spacing
            }
        } catch (\PDOException $e) {
            echo "\033[1;31mMigration failed for table '$tableName': " . $e->getMessage() . "\033[0m\n\n"; // Red for error, with spacing
        }
    }

    // Generates the constraint name for the foreign key
    private function generateConstraintName($tableName, $columnName)
    {
        return $tableName . "_" . $columnName . "_foreign";
    }

    // Check if the foreign key constraint exists in the database
    private function checkConstraintExists($tableName, $constraintName)
    {
        $sql = "SELECT COUNT(*) AS count from INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = '$tableName' AND CONSTRAINT_NAME = '$constraintName' AND CONSTRAINT_TYPE = 'FOREIGN KEY'";
        $result = $this->db->getConnection()->query($sql);
        $row = $result->fetch();
        return $row['count'] > 0;
    }

    // Generate the SQL for the foreign key, including CASCADE actions
    private function generateForeignKeySQL($tableName, $columnName, $refTableName, $refColumnName)
    {
        $constraintName = $this->generateConstraintName($tableName, $columnName);

        // Generate the foreign key SQL with CASCADE on DELETE and UPDATE
        $sql = "ALTER TABLE $tableName ADD CONSTRAINT $constraintName FOREIGN KEY ($columnName) REFERENCES $refTableName($refColumnName) ON DELETE CASCADE ON UPDATE CASCADE";

        return $sql;
    }

}