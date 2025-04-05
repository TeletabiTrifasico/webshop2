<?php

namespace App\Models;

class OrderItem extends Model {
    protected $table = 'order_items';
    
    // Create new order item - modified to match your actual database schema
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['order_id'],
                $data['product_id'],
                $data['quantity'],
                $data['price']
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (\Exception $e) {
            error_log("OrderItem creation error: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Get items for a specific order
    public function getByOrderId($orderId) {
        try {
            $query = "
                SELECT oi.*, p.name as product_name, p.image as product_image
                FROM {$this->table} oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$orderId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error getting order items: " . $e->getMessage());
            return [];
        }
    }
}