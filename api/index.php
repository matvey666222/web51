<?php
// Включение обработки ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0); // В продакшене установить в 0

// Подключение конфигурации
require_once 'config.php';
require_once 'cors.php';

// Обработка CORS
handleCORS();

// Установка заголовков JSON
header('Content-Type: application/json; charset=utf-8');

// Основной класс API
class MatveyProjectAPI {
    private $baseUrl;
    private $cacheDir;
    
    public function __construct() {
        $this->baseUrl = BASE_URL;
        $this->cacheDir = __DIR__ . '/cache/';
        
        // Создание директории для кэша, если не существует
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Получение данных с оригинального API
     */
    private function fetchFromSource($endpoint = '', $params = []) {
        $url = $this->baseUrl . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $cacheKey = md5($url);
        $cacheFile = $this->cacheDir . $cacheKey . '.json';
        
        // Проверка кэша
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < CACHE_DURATION) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        // Инициализация cURL
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'MatveyProject API Proxy/1.0',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: " . $httpCode);
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON decode error: " . json_last_error_msg());
        }
        
        // Сохранение в кэш
        file_put_contents($cacheFile, json_encode($data));
        
        return $data;
    }
    
    /**
     * Форматирование ответа API
     */
    private function sendResponse($data, $success = true, $message = '', $code = HTTP_OK) {
        http_response_code($code);
        
        $response = [
            'success' => $success,
            'timestamp' => date('c'),
            'api_version' => API_VERSION
        ];
        
        if ($success) {
            $response['data'] = $data;
            if ($message) {
                $response['message'] = $message;
            }
        } else {
            $response['error'] = $data;
            if ($message) {
                $response['message'] = $message;
            }
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Получение всех данных
     */
    public function getAllData() {
        try {
            $data = $this->fetchFromSource();
            $this->sendResponse($data, true, 'Data retrieved successfully');
        } catch (Exception $e) {
            $this->sendResponse($e->getMessage(), false, 'Failed to fetch data', HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Получение данных с фильтрацией
     */
    public function getFilteredData($filters) {
        try {
            $data = $this->fetchFromSource('', $filters);
            
            // Дополнительная фильтрация на нашей стороне
            if (isset($filters['search'])) {
                $data = $this->filterBySearch($data, $filters['search']);
            }
            
            $this->sendResponse($data, true, 'Filtered data retrieved successfully');
        } catch (Exception $e) {
            $this->sendResponse($e->getMessage(), false, 'Failed to fetch filtered data', HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Фильтрация данных по поисковому запросу
     */
    private function filterBySearch($data, $search) {
        if (!is_array($data)) {
            return $data;
        }
        
        $result = [];
        $search = strtolower($search);
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $filtered = $this->filterBySearch($value, $search);
                if (!empty($filtered)) {
                    $result[$key] = $filtered;
                }
            } else {
                if (stripos(strval($value), $search) !== false || 
                    stripos(strval($key), $search) !== false) {
                    $result[$key] = $value;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Получение информации о API
     */
    public function getApiInfo() {
        $info = [
            'name' => API_NAME,
            'version' => API_VERSION,
            'endpoints' => [
                'GET /' => 'API information',
                'GET /data' => 'Get all data from MatveyProject',
                'GET /data?filter=value' => 'Get filtered data',
                'GET /health' => 'API health check'
            ],
            'timestamp' => date('c'),
            'source' => BASE_URL
        ];
        
        $this->sendResponse($info, true, 'API information');
    }
    
    /**
     * Проверка здоровья API
     */
    public function healthCheck() {
        try {
            $data = $this->fetchFromSource();
            $status = [
                'status' => 'healthy',
                'source_api' => 'reachable',
                'timestamp' => date('c')
            ];
            $this->sendResponse($status, true, 'API is healthy');
        } catch (Exception $e) {
            $status = [
                'status' => 'degraded',
                'source_api' => 'unreachable',
                'error' => $e->getMessage(),
                'timestamp' => date('c')
            ];
            $this->sendResponse($status, false, 'API is degraded', HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

// Обработка запроса
$api = new MatveyProjectAPI();
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$queryParams = $_GET;

// Маршрутизация
switch ($requestMethod) {
    case 'GET':
        if ($requestUri === '/api/' || $requestUri === '/api' || $requestUri === '/') {
            $api->getApiInfo();
        } elseif ($requestUri === '/api/data' || $requestUri === '/data') {
            if (!empty($queryParams)) {
                $api->getFilteredData($queryParams);
            } else {
                $api->getAllData();
            }
        } elseif ($requestUri === '/api/health' || $requestUri === '/health') {
            $api->healthCheck();
        } else {
            $api->sendResponse('Endpoint not found', false, 'The requested endpoint does not exist', HTTP_NOT_FOUND);
        }
        break;
        
    case 'POST':
        // Обработка POST запросов (если нужно)
        $input = json_decode(file_get_contents('php://input'), true);
        $api->sendResponse(['method' => 'POST', 'input' => $input], true, 'POST request received');
        break;
        
    default:
        $api->sendResponse('Method not allowed', false, 'HTTP method not supported', HTTP_METHOD_NOT_ALLOWED);
        break;
}
?>