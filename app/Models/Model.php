<?php

namespace App\Models;

abstract class Model {
    protected $pdo;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        $this->connectToDatabase();
    }

    protected function connectToDatabase() {
        try {
            // Get database configuration
            $host = getenv('DB_HOST') ?: 'db';
            $dbname = getenv('DB_NAME') ?: 'webshop_db';
            $username = getenv('DB_USER') ?: 'webshopadmin';
            $password = getenv('DB_PASSWORD') ?: '!webshopadmin2025';
            
            // Log the connection attempt for debugging
            error_log("DB Connection attempt - Host: $host, Database: $dbname, User: $username");
            
            // Create the connection with options
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->pdo = new \PDO($dsn, $username, $password, $options);
            error_log("Database connection successfully established");
        } catch (\PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new \Exception("Database connection error: " . $e->getMessage());
        }
    }

    public function findById($id) {
        try {
            if (!$this->pdo) {
                $this->connectToDatabase(); // Reconnect if connection is lost
            }
            
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
            $stmt->execute([(int)$id]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log("Error finding record by ID: " . $e->getMessage());
            throw new \Exception("Database query error: " . $e->getMessage());
        }
    }

    public function create($data) {
        try {
            if (!$this->pdo) {
                $this->connectToDatabase();
            }
            
            $fields = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})"
            );
            
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error creating record: " . $e->getMessage());
            error_log("Data: " . json_encode($data));
            throw new \Exception("Error creating record: " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        try {
            if (!$this->pdo) {
                $this->connectToDatabase();
            }
            
            $setClause = '';
            $values = [];
            
            foreach ($data as $field => $value) {
                $setClause .= "{$field} = ?, ";
                $values[] = $value;
            }
            
            $setClause = rtrim($setClause, ', ');
            $values[] = $id;
            
            $stmt = $this->pdo->prepare(
                "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?"
            );
            
            return $stmt->execute($values);
        } catch (\PDOException $e) {
            error_log("Error updating record: " . $e->getMessage());
            error_log("Data: " . json_encode($data));
            throw new \Exception("Error updating record: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            if (!$this->pdo) {
                $this->connectToDatabase();
            }
            
            $stmt = $this->pdo->prepare(
                "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?"
            );
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Error deleting record: " . $e->getMessage());
            throw new \Exception("Error deleting record: " . $e->getMessage());
        }
    }

    public function paginate($page = 1, $perPage = 15, $conditions = []) {
        try {
            if (!$this->pdo) {
                $this->connectToDatabase();
            }
            
            $page = max(1, (int)$page);
            $perPage = max(1, (int)$perPage);
            $offset = ($page - 1) * $perPage;
            
            $where = '';
            $params = [];
            
            if (!empty($conditions)) {
                $whereClauses = [];
                foreach ($conditions as $field => $value) {
                    $whereClauses[] = "{$field} = ?";
                    $params[] = $value;
                }
                $where = 'WHERE ' . implode(' AND ', $whereClauses);
            }
            
            // Get total count
            $countStmt = $this->pdo->prepare(
                "SELECT COUNT(*) as total FROM {$this->table} {$where}"
            );
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Get paginated results
            $queryParams = $params;
            $queryParams[] = $perPage;
            $queryParams[] = $offset;
            
            $stmt = $this->pdo->prepare(
                "SELECT * FROM {$this->table} {$where} LIMIT ? OFFSET ?"
            );
            
            // Handle integer parameters properly
            foreach ($queryParams as $key => $value) {
                if (is_int($value) || (is_string($value) && ctype_digit($value))) {
                    $stmt->bindValue($key + 1, (int)$value, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key + 1, $value);
                }
            }
            
            $stmt->execute();
            
            return [
                'data' => $stmt->fetchAll(),
                'total' => $total,
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ];
        } catch (\PDOException $e) {
            error_log("Error in pagination: " . $e->getMessage());
            throw new \Exception("Error fetching paginated data: " . $e->getMessage());
        }
    }

    // Transaction methods
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }
}