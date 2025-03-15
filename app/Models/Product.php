<?php

namespace App\Models;

class Product extends Model {
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM products ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO products (name, description, price, image) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['image']
        ]);
    }

    public function update($id, $data) {
        // Check if image is included in update
        if (!isset($data['image']) || empty($data['image'])) {
            $stmt = $this->pdo->prepare("
                UPDATE products 
                SET name = ?, description = ?, price = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['name'],
                $data['description'],
                $data['price'],
                $id
            ]);
        }

        $stmt = $this->pdo->prepare("
            UPDATE products 
            SET name = ?, description = ?, price = ?, image = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['image'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getById($id) {  // Add this method
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}