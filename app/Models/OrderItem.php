<?php

namespace App\Models;

class OrderItem extends Model {
    protected $table = 'order_items';

    public function getByOrderId($orderId) {
        $stmt = $this->pdo->prepare("
            SELECT oi.*, p.name as product_name, p.image as product_image
            FROM {$this->table} oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
}