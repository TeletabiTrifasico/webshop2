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
}