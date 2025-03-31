<?php

namespace App\Models;

class Cart extends Model {
    protected $table = 'cart_items';

    public function getByUserId($userId) {
        $query = "
            SELECT ci.id, ci.product_id, ci.quantity, p.name, p.price, p.image 
            FROM {$this->table} ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function addItem($userId, $productId, $quantity) {
        // Check if item already exists
        $stmt = $this->pdo->prepare("
            SELECT id, quantity FROM {$this->table} 
            WHERE user_id = ? AND product_id = ?
        ");
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update quantity
            $newQuantity = $existing['quantity'] + $quantity;
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table} SET quantity = ?
                WHERE id = ?
            ");
            return $stmt->execute([$newQuantity, $existing['id']]);
        } else {
            // Insert new item
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (user_id, product_id, quantity)
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$userId, $productId, $quantity]);
        }
    }

    public function updateQuantity($userId, $itemId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($userId, $itemId);
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} SET quantity = ?
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$quantity, $itemId, $userId]);
    }

    public function removeItem($userId, $itemId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->table} 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$itemId, $userId]);
    }

    public function clearCart($userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->table} 
            WHERE user_id = ?
        ");
        return $stmt->execute([$userId]);
    }

    public function getTotal($userId) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(ci.quantity * p.price) as total
            FROM {$this->table} ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 0;
    }
}