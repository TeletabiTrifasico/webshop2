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
            SELECT o.*, u.username 
            FROM {$this->table} o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([(int)$orderId]);
        return $stmt->fetch();
    }

    public function create($data) {
        $this->pdo->beginTransaction();
        
        try {
            // Calculate total amount from cart items
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalAmount += ($item['price'] * $item['quantity']);
            }

            // Create order record
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (user_id, total_amount, status, created_at)
                VALUES (?, ?, 'pending', NOW())
            ");
            
            $stmt->execute([$data['user_id'], $totalAmount]);
            $orderId = $this->pdo->lastInsertId();
            
            // Create order items
            foreach ($data['items'] as $item) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $orderId, 
                    $item['product_id'], 
                    $item['quantity'], 
                    $item['price']
                ]);
            }
            
            $this->pdo->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
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
        $this->pdo->beginTransaction();
        
        try {
            // First delete related order items
            $stmt = $this->pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$id]);
            
            // Then delete the order
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
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