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
        try {
            $rawData = file_get_contents('php://input');
            error_log('Raw request data: ' . $rawData);
            
            $data = json_decode($rawData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('JSON decode error: ' . json_last_error_msg());
                throw new \Exception('Invalid JSON data');
            }
            
            error_log('Parsed request data: ' . print_r($data, true));
            return $data ?: [];
        } catch (\Exception $e) {
            error_log('Request data error: ' . $e->getMessage());
            return [];
        }
    }
}