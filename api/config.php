<?php
// Конфигурация API
define('API_NAME', 'MatveyProject API Proxy');
define('API_VERSION', '1.0');
define('BASE_URL', 'https://matveyproject.ru/api/v2');
define('CACHE_DURATION', 300); // 5 минут в секундах

// Настройки CORS
define('ALLOWED_ORIGINS', [
    'http://localhost',
    'https://yourdomain.com',
    // Добавьте ваши домены
]);

// Коды ответов HTTP
define('HTTP_OK', 200);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_INTERNAL_SERVER_ERROR', 500);
?>