<?php

namespace App\Models;

class Model {
    protected $pdo;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    protected function executeWithIntParams($query, $params = []) {
        $stmt = $this->pdo->prepare($query);
        
        foreach ($params as $key => $value) {
            // If this is a numeric index for a LIMIT or OFFSET, bind as integer
            if (is_integer($value) || (is_string($value) && is_numeric($value))) {
                $stmt->bindValue($key + 1, (int)$value, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key + 1, $value);
            }
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function toArray($data) {
        return json_decode(json_encode($data), true);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})"
        );
        
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $fields = implode(' = ?, ', array_keys($data)) . ' = ?';
        
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->table} SET {$fields} WHERE {$this->primaryKey} = ?"
        );
        
        $values = array_values($data);
        $values[] = $id;
        
        return $stmt->execute($values);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([$id]);
    }

    public function paginate($page = 1, $perPage = 15, $conditions = []) {
        $page = (int)$page;
        $perPage = (int)$perPage;
        $offset = ($page - 1) * $perPage;
        
        $where = '';
        $params = [];
        
        if (!empty($conditions)) {
            $where = 'WHERE ' . implode(' AND ', array_map(function($field) {
                return "$field = ?";
            }, array_keys($conditions)));
            $params = array_values($conditions);
        }
        
        // Get total count
        $countParams = $params;
        $countStmt = $this->executeWithIntParams(
            "SELECT COUNT(*) as total FROM {$this->table} {$where}",
            $countParams
        );
        $total = $countStmt->fetchColumn();
        
        // Add limit and offset to params
        $params[] = $perPage;
        $params[] = $offset;
        
        // Get paginated results
        $stmt = $this->executeWithIntParams(
            "SELECT * FROM {$this->table} {$where} LIMIT ? OFFSET ?",
            $params
        );
        
        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }
}