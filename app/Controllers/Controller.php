<?php

namespace App\Controllers;

class Controller {
    protected $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    protected function view($name, $data = []) {
        extract($data);
        require __DIR__ . "/../../app/views/{$name}.php";
    }

    protected function model($name) {
        $modelClass = "\\App\\Models\\$name";
        return new $modelClass();
    }

    protected function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    protected function getRequestData() {
        return json_decode(file_get_contents('php://input'), true);
    }
}