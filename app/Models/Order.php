<?php

namespace App\Models;

class Order extends Model {
    protected $table = 'orders';

    public function getAll() {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.username
            FROM {$this->table} o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllWithUserDetails($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                $whereClause = "WHERE o.id LIKE ? OR u.username LIKE ?";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $query = "
                SELECT o.*, u.username, u.email 
                FROM {$this->table} o
                JOIN users u ON o.user_id = u.id
                {$whereClause}
                ORDER BY o.created_at DESC
                LIMIT ?, ?
            ";
            
            $params[] = (int)$offset;
            $params[] = (int)$limit;
            
            $stmt = $this->pdo->prepare($query);
            
            // Bind parameters with explicit types
            foreach ($params as $i => $param) {
                if (is_int($param)) {
                    $stmt->bindValue($i + 1, $param, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($i + 1, $param);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error getting orders: " . $e->getMessage());
            return [];
        }
    }

    public function getTotal($search = '') {
        try {
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                $whereClause = "WHERE o.id LIKE ? OR u.username LIKE ?";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $query = "
                SELECT COUNT(*) as total 
                FROM {$this->table} o
                JOIN users u ON o.user_id = u.id
                {$whereClause}
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error getting order count: " . $e->getMessage());
            return 0;
        }
    }

    public function getWithDetails($orderId) {
        try {
            $query = "
                SELECT o.*, u.username, u.email 
                FROM {$this->table} o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$orderId]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            error_log("Error getting order details: " . $e->getMessage());
            return false;
        }
    }

    public function create($data) {
        try {
            // Start transaction to ensure atomicity
            $this->pdo->beginTransaction();
            
            // Adjusted fields to match your database schema
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (user_id, total_amount, status, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['user_id'],
                $data['total_amount'],
                $data['status']
            ]);
            
            $orderId = $this->pdo->lastInsertId();
            
            $this->pdo->commit();
            return $orderId;
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Order creation error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUserOrders($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        
        $stmt->bindValue(1, (int)$userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalUserOrders($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total FROM {$this->table} 
            WHERE user_id = ?
        ");
        $stmt->execute([(int)$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.username, u.email
            FROM {$this->table} o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatus($orderId, $status) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table} 
                SET status = ? 
                WHERE id = ?
            ");
            return $stmt->execute([$status, $orderId]);
        } catch (\PDOException $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            
            // First delete associated order items
            $itemStmt = $this->pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
            $itemStmt->execute([(int)$id]);
            
            // Then delete the order
            $orderStmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $orderStmt->execute([(int)$id]);
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Order deletion error: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalRevenue() {
        $stmt = $this->pdo->prepare("
            SELECT SUM(total_amount) as total FROM {$this->table}
        ");
        $stmt->execute();
        return (float)($stmt->fetchColumn() ?: 0);
    }
}