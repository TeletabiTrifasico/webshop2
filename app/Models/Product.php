<?php

namespace App\Models;

class Product extends Model {
    protected $table = 'products';
    
    // Get all products with pagination
    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT * FROM {$this->table}";
            $params = [];
            
            // Add search functionality
            if (!empty($search)) {
                $query .= " WHERE name LIKE ? OR description LIKE ?";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            
            $stmt = $this->pdo->prepare($query);
            
            // Explicitly bind parameters one by one
            $paramIndex = 1;
            
            // Bind search params if they exist
            if (!empty($search)) {
                $stmt->bindValue($paramIndex++, "%{$search}%");
                $stmt->bindValue($paramIndex++, "%{$search}%");
            }
            
            // Bind limit and offset as integers
            $stmt->bindValue($paramIndex++, (int)$limit, \PDO::PARAM_INT);
            $stmt->bindValue($paramIndex, (int)$offset, \PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log("Product::getAll error: " . $e->getMessage());
            error_log("Query parameters: page={$page}, limit={$limit}, search={$search}");
            throw $e; // Re-throw to be caught by the controller
        }
    }
    
    // Get total count of products (for pagination)
    public function getTotal($search = '') {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table}";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE name LIKE ? OR description LIKE ?";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            $stmt = $this->pdo->prepare($query);
            
            for ($i = 0; $i < count($params); $i++) {
                $stmt->bindValue($i + 1, $params[$i]);
            }
            
            $stmt->execute();
            $result = $stmt->fetch();
            return (int)$result['total'];
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log("Product::getTotal error: " . $e->getMessage());
            throw $e; // Re-throw to be caught by the controller
        }
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->bindValue(1, (int)$id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getLatest($limit = 4) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ?");
        // PDO requires integers for LIMIT, so we need to explicitly bind as integer
        $stmt->bindValue(1, (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (name, description, price, image, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['image']
        ]) ? $this->pdo->lastInsertId() : null;
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET name = ?, description = ?, price = ?, image = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['image'],
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}