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
}