<?php

namespace App\Models;

class Product extends Model {
    protected $table = 'products';

    public function getLatest($limit = 4) {
        $limit = (int)$limit;
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT {$limit}";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (name, description, price, image, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['image']
        ]) ? $this->pdo->lastInsertId() : null;
    }
    
    public function update($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table}
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
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}