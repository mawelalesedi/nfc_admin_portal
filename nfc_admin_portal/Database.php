<?php
/**
 * Database Connection Class
 */

class Database {
    private $connection;
    private $error;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        // Enable error reporting but allow us to handle the connection manually
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 
        
        try {
            // Connect to MySQL server (handling XAMPP root with no password by default)
            $this->connection = @new mysqli(DB_HOST, DB_USER, DB_PASS);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }

            // Check if database exists, create it if missing
            if (!@$this->connection->select_db(DB_NAME)) {
                $dbName = $this->connection->real_escape_string(DB_NAME);
                $create_query = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                if (!$this->connection->query($create_query) || !$this->connection->select_db(DB_NAME)) {
                    throw new Exception("Database '" . DB_NAME . "' could not be selected/created.");
                }
            }
            
            $this->connection->set_charset("utf8mb4");
        } catch (mysqli_sql_exception | Exception $e) {
            $this->error = $e->getMessage();
            throw new Exception("Database Connection Failed: " . $this->error);
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        $result = $this->connection->query($sql);
        
        if ($result === false) {
            throw new Exception("Database Query Error: " . $this->connection->error);
        }
        
        return $result;
    }

    public function importSqlFile($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("SQL file not found at: " . $filePath);
        }
        
        $sql = file_get_contents($filePath);
        
        // Execute multi-query to handle the entire dump at once
        if ($this->connection->multi_query($sql)) {
            do {
                if ($result = $this->connection->store_result()) {
                    $result->free();
                }
            } while ($this->connection->more_results() && $this->connection->next_result());
            return true;
        }
        return false;
    }

    public function tableExists($tableName) {
        try {
            $result = $this->connection->query("SHOW TABLES LIKE '" . $this->escape($tableName) . "'");
            return $result && $result->num_rows > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function prepare($sql) {
        try {
            $stmt = $this->connection->prepare($sql);
            if ($stmt === false) {
                throw new Exception($this->connection->error);
            }
            return $stmt;
        } catch (mysqli_sql_exception | Exception $e) {
            $error = $e->getMessage();
            if (strpos($error, "doesn't exist") !== false) {
                throw new Exception("Critical Error: The database '" . DB_NAME . "' is missing required tables. " . 
                                    "System reported: '" . $error . "'. You must import the 'nfc_patrol_admin.sql' file " .
                                    "into your MySQL database to resolve this.");
            }
            throw new Exception("Database Prepare Error for '" . DB_NAME . "': " . $error);
        }
    }

    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    public function getLastInsertId() {
        return $this->connection->insert_id;
    }

    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }

    public function getError() {
        return $this->error;
    }

    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
