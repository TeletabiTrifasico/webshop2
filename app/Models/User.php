<?php

namespace App\Models;

class User extends Model {
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password, role) 
            VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password'],
            $data['role'] ?? 'user'
        ]) ? $this->pdo->lastInsertId() : null;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}