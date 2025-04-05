<?php

namespace App\Models;

class Cart extends Model {
    protected $table = 'cart_items';

    public function getCartItems($userId) {
        try {
            $query = "
                SELECT ci.id, ci.product_id, ci.quantity, p.name, p.price, p.image 
                FROM {$this->table} ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.user_id = ?
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([(int)$userId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error getting cart items: " . $e->getMessage());
            throw new \Exception("Error retrieving cart items: " . $e->getMessage());
        }
    }

    public function addItem($userId, $productId, $quantity) {
        try {
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
        } catch (\PDOException $e) {
            error_log("Error adding cart item: " . $e->getMessage());
            throw new \Exception("Error adding item to cart: " . $e->getMessage());
        }
    }

    public function updateItem($itemId, $userId, $quantity) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table} SET quantity = ? 
                WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([(int)$quantity, (int)$itemId, (int)$userId]);
        } catch (\PDOException $e) {
            error_log("Error updating cart item: " . $e->getMessage());
            throw new \Exception("Error updating cart item: " . $e->getMessage());
        }
    }

    public function removeItem($itemId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM {$this->table} 
                WHERE id = ? AND user_id = ?
            ");
            return $stmt->execute([(int)$itemId, (int)$userId]);
        } catch (\PDOException $e) {
            error_log("Error removing cart item: " . $e->getMessage());
            throw new \Exception("Error removing cart item: " . $e->getMessage());
        }
    }

    public function clearCart($userId) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM {$this->table} 
                WHERE user_id = ?
            ");
            return $stmt->execute([(int)$userId]);
        } catch (\PDOException $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            throw new \Exception("Error clearing cart: " . $e->getMessage());
        }
    }

    public function getTotal($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT SUM(ci.quantity * p.price) as total
                FROM {$this->table} ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.user_id = ?
            ");
            $stmt->execute([(int)$userId]);
            return (float)($stmt->fetchColumn() ?: 0);
        } catch (\PDOException $e) {
            error_log("Error calculating cart total: " . $e->getMessage());
            throw new \Exception("Error calculating cart total: " . $e->getMessage());
        }
    }
}