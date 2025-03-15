<?php

namespace App\Models;

class Order extends Model {
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

    public function create($data) {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (user_id, total_amount, status) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $data['user_id'],
                $data['total_amount'],
                $data['status']
            ]);
            $orderId = $this->pdo->lastInsertId();

            // Insert order items
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

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function getItems($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT oi.*, p.name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
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
}