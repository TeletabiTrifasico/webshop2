<?php

namespace App\Models;

class Order extends Model {
    protected $table = 'orders';

    public function getAll() {
        $stmt = $this->pdo->query("
            SELECT o.*, u.username 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getAllWithUserDetails() {
        return $this->getAll(); // Using existing method as it already includes user details
    }

    public function getByIdWithUserDetails($id) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.username, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getWithDetails($id) {
        $stmt = $this->pdo->prepare(
            "SELECT o.*, u.username, u.email,
             (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.id = ?"
        );
        
        $stmt->execute([$id]);
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

    public function getItems($orderId) {
        $stmt = $this->pdo->prepare(
            "SELECT oi.*, p.name, p.image
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?"
        );
        
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getOrderItems($id) {
        $stmt = $this->pdo->prepare("
            SELECT oi.*, p.name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function getByUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}