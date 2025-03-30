<?php

namespace App\Models;

class Product extends Model {
    protected $table = 'products';

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
            INSERT INTO {$this->table} (name, description, price, image) 
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
                UPDATE {$this->table} 
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

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getFeatured($limit = 8) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE featured = 1 LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function search($query, $page = 1, $perPage = 15) {
        $offset = ($page - 1) * $perPage;
        $search = "%{$query}%";
        
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} 
             WHERE name LIKE ? OR description LIKE ? 
             LIMIT ? OFFSET ?"
        );
        
        $stmt->execute([$search, $search, $perPage, $offset]);
        return $stmt->fetchAll();
    }

    public function getAllCategories() {
        $stmt = $this->pdo->query(
            "SELECT DISTINCT category FROM {$this->table} WHERE category IS NOT NULL"
        );
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getByCategory($category, $page = 1, $perPage = 15) {
        return $this->paginate($page, $perPage, ['category' => $category]);
    }

    public function getLowStock($threshold = 5) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE stock <= ? ORDER BY stock ASC"
        );
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }
}