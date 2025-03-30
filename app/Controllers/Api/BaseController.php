<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;

class BaseController extends Controller {
    protected function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    protected function errorResponse($message, $statusCode = 400) {
        $this->jsonResponse(['error' => $message], $statusCode);
    }

    protected function successResponse($data = [], $message = '') {
        $response = ['success' => true];
        if ($message) $response['message'] = $message;
        if ($data) $response['data'] = $data;
        $this->jsonResponse($response);
    }
}