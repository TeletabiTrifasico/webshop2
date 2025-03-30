<?php

namespace App\Models;

class Product extends Model {
    protected $table = 'products';

    public function getLatest($limit = 4) {
        try {
            if (!$this->pdo) {
                throw new \Exception("Database connection not established");
            }

            // Convert limit to integer to prevent SQL injection
            $limit = (int)$limit;
            
            $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT {$limit}";
            $stmt = $this->pdo->query($sql);
            
            if (!$stmt) {
                throw new \Exception("Failed to execute query");
            }

            $products = $stmt->fetchAll();
            error_log("Found " . count($products) . " products");

            return $products;
        } catch (\PDOException $e) {
            error_log("PDO Error: " . $e->getMessage());
            throw new \Exception("Database error: " . $e->getMessage());
        }
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
}