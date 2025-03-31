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

    public function getAllWithUserDetails() {
        return $this->getAll(); // Using existing method as it already includes user details
    }

    public function getWithDetails($id) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.username, u.email
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
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
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.username, u.email
            FROM {$this->table} o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} SET status = ? WHERE id = ?
        ");
        return $stmt->execute([$status, $id]);
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
}