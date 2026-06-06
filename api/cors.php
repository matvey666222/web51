<?php
// Обработка CORS заголовков
function handleCORS() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, ALLOWED_ORIGINS)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        header("Access-Control-Allow-Origin: *");
    }
    
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 3600");
    
    // Обработка preflight OPTIONS запроса
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(HTTP_OK);
        exit();
    }
}
?>