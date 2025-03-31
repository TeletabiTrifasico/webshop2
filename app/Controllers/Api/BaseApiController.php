<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;

class BaseApiController extends Controller {
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function getRequestData() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // For PUT requests, get data from php://input
        if ($method === 'PUT') {
            // Check if Content-Type contains multipart/form-data
            if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false) {
                // Handle multipart form data for PUT
                parse_str(file_get_contents('php://input'), $_PUT);
                
                // Merge with $_FILES for uploaded files
                $data = $_PUT;
                // Files are already in $_FILES
            } else {
                // Regular JSON data
                $input = file_get_contents('php://input');
                $data = json_decode($input, true) ?? [];
            }
        } 
        // For POST requests, check if it's multipart/form-data
        else if ($method === 'POST') {
            if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false) {
                // For multipart/form-data, data is in $_POST
                $data = $_POST;
                // Files are in $_FILES
            } else if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                // For JSON data
                $input = file_get_contents('php://input');
                $data = json_decode($input, true) ?? [];
            } else {
                // Default to $_POST
                $data = $_POST;
            }
        } else {
            // For other methods
            $input = file_get_contents('php://input');
            $data = json_decode($input, true) ?? [];
        }
        
        return $data;
    }
    
    protected function model($model) {
        $model = "\\App\\Models\\$model";
        return new $model();
    }
}