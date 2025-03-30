<?php

namespace App\Models;

class Cart extends Model {
    protected $table = 'cart_items';

    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare("
            SELECT ci.*, p.name, p.price, p.image
            FROM {$this->table} ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function addItem($userId, $productId, $quantity = 1) {
        // Check if item already exists
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = ? AND product_id = ?
        ");
        $stmt->execute([$userId, $productId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update quantity
            return $this->update($existing['id'], [
                'quantity' => $existing['quantity'] + $quantity
            ]);
        }

        // Add new item
        return $this->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
    }

    public function updateQuantity($userId, $itemId, $quantity) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
            SET quantity = ?
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