<?php

namespace App\Models;

class Order extends Model {
    protected $table = 'orders';

    public function getAll() {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.username
            FROM {$this->table} o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllWithUserDetails($page = 1, $limit = 10, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT o.*, u.username FROM {$this->table} o 
                  JOIN users u ON o.user_id = u.id";
        $params = [];
        
        // Add search functionality
        if (!empty($search)) {
            $query .= " WHERE o.id LIKE ? OR u.username LIKE ? OR o.status LIKE ?";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $query .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->pdo->prepare($query);
        
        // Bind parameters
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        
        // Bind limit and offset as integers
        $stmt->bindValue(count($params) + 1, (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, (int)$offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotal($search = '') {
        $query = "SELECT COUNT(*) as total FROM {$this->table} o JOIN users u ON o.user_id = u.id";
        $params = [];
        
        if (!empty($search)) {
            $query .= " WHERE o.id LIKE ? OR u.username LIKE ? OR o.status LIKE ?";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $stmt = $this->pdo->prepare($query);
        
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }

    public function getWithDetails($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.username, u.email 
            FROM {$this->table} o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([(int)$orderId]);
        return $stmt->fetch();
    }

    public function create($data) {
        try {
            // Start transaction to ensure atomicity
            $this->pdo->beginTransaction();
            
            // Adjusted fields to match your database schema
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (user_id, total_amount, status, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['user_id'],
                $data['total_amount'],
                $data['status']
            ]);
            
            $orderId = $this->pdo->lastInsertId();
            
            $this->pdo->commit();
            return $orderId;
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Order creation error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUserOrders($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        
        $stmt->bindValue(1, (int)$userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalUserOrders($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM {$this->table} 
            WHERE user_id = ?
        ");
        $stmt->execute([(int)$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.username, u.email
            FROM {$this->table} o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatus($orderId, $status) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} SET status = ? WHERE id = ?
        ");
        return $stmt->execute([$status, (int)$orderId]);
    }

    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            
            // First delete associated order items
            $itemStmt = $this->pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
            $itemStmt->execute([(int)$id]);
            
            // Then delete the order
            $orderStmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $orderStmt->execute([(int)$id]);
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Order deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalRevenue() {
        $stmt = $this->pdo->prepare("
            SELECT SUM(total_amount) as total FROM {$this->table}
        ");
        $stmt->execute();
        return (float)($stmt->fetchColumn() ?: 0);
    }
}