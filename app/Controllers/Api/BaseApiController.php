<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;

class BaseApiController extends Controller {
    protected function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        http_response_code($status);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    protected function getRequestData() {
        return json_decode(file_get_contents('php://input'), true);
    }
}