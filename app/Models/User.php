<?php

namespace App\Models;

class User extends Model {
    protected $table = 'users';
    
    // Get all users with pagination
    public function getAll($page = 1, $limit = 10, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM {$this->table}";
        $params = [];
        
        // Add search functionality
        if (!empty($search)) {
            $query .= " WHERE username LIKE ? OR email LIKE ?";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($query);
        
        // Bind parameters with specific types
        if (!empty($params)) {
            foreach ($params as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
        }
        
        // Bind limit and offset as integers
        $stmt->bindValue(count($params) + 1, (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, (int)$offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Get total count of users (for pagination)
    public function getTotal($search = '') {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($search)) {
            $query .= " WHERE username LIKE ? OR email LIKE ?";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (username, email, password, role) 
            VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password'],
            $data['role']
        ]) ? $this->pdo->lastInsertId() : null;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET " . implode(', ', $fields) . "
            WHERE id = ?
        ");
        
        return $stmt->execute($values);
    }
    
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}