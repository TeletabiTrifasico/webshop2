<?php

namespace App\Models;

class Cart extends Model {
    protected $table = 'cart_items';

    public function getCartItems($userId) {
        $query = "
            SELECT ci.id, ci.product_id, ci.quantity, p.name, p.price, p.image 
            FROM {$this->table} ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([(int)$userId]);
        return $stmt->fetchAll();
    }

    public function addItem($userId, $productId, $quantity) {
        // Check if item already exists
        $stmt = $this->pdo->prepare("
            SELECT id, quantity FROM {$this->table} 
            WHERE user_id = ? AND product_id = ?
        ");
        $stmt->execute([(int)$userId, (int)$productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table} SET quantity = ? 
                WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([(int)$newQuantity, (int)$existing['id'], (int)$userId]);
        } else {
            // Insert new item
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (user_id, product_id, quantity) 
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([(int)$userId, (int)$productId, (int)$quantity]);
        }
    }

    public function updateItem($itemId, $userId, $quantity) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} SET quantity = ? 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([(int)$quantity, (int)$itemId, (int)$userId]);
    }

    public function removeItem($itemId, $userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->table} 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([(int)$itemId, (int)$userId]);
    }

    public function clearCart($userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->table} 
            WHERE user_id = ?
        ");
        return $stmt->execute([(int)$userId]);
    }

    public function getTotal($userId) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(ci.quantity * p.price) as total
            FROM {$this->table} ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ");
        $stmt->execute([(int)$userId]);
        return (float)($stmt->fetchColumn() ?: 0);
    }
}