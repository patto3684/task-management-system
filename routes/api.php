<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/TaskController.php';

function sendJsonResponse($success, $message, $data = [], $errors = [], $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'errors' => $errors,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

function parseRequestRoute(): string
{
    if (!empty($_GET['route'])) {
        return trim((string) $_GET['route'], '/');
    }

    $requestUri = rawurldecode($_SERVER['REQUEST_URI'] ?? '/');
    $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
    $scriptName = rawurldecode($_SERVER['SCRIPT_NAME'] ?? '');
    $basePath = dirname($scriptName);

    if ($basePath !== '/' && $basePath !== '.' && strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }

    if (strpos($path, '/api') === 0) {
        $path = substr($path, 4);
    }

    return trim($path, '/');
}

function readRequestJson(): array
{
    $rawInput = file_get_contents('php://input');
    if ($rawInput === false || trim($rawInput) === '') {
        return [];
    }

    $decoded = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('Invalid JSON payload.');
    }

    if (!is_array($decoded)) {
        return [];
    }

    return $decoded;
}

function routeApi(PDO $pdo): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $route = parseRequestRoute();
    $segments = $route === '' ? [] : explode('/', $route);
    $resource = $segments[0] ?? null;
    $resourceId = $segments[1] ?? null;

    if ($resource !== 'tasks') {
        sendJsonResponse(false, 'Route not found', [], [], 404);
    }

    try {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($resourceId !== null && $resourceId !== '') {
                if (!Task::validateId($resourceId)) {
                    sendJsonResponse(false, 'Task ID must be a positive integer.', [], ['id' => 'Invalid task ID.'], 400);
                }

                $task = TaskController::getById($pdo, (int) $resourceId);
                if (!$task) {
                    sendJsonResponse(false, 'Task not found', [], [], 404);
                }

                sendJsonResponse(true, 'Task retrieved successfully', $task, [], 200);
            }

            $filters = [
                'status' => $_GET['status'] ?? null,
                'priority' => $_GET['priority'] ?? null,
                'search' => $_GET['search'] ?? null,
            ];

            $tasks = TaskController::getAll($pdo, $filters);
            sendJsonResponse(true, 'Tasks retrieved successfully', $tasks, [], 200);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($resourceId !== null && $resourceId !== '') {
                sendJsonResponse(false, 'Use POST /tasks to create a task.', [], [], 400);
            }

            $payload = readRequestJson();
            $validation = Task::validatePayload($payload, false);

            if (!$validation['valid']) {
                sendJsonResponse(false, 'Validation failed', [], $validation['errors'], 400);
            }

            $createdTask = TaskController::create($pdo, $validation['data']);
            sendJsonResponse(true, 'Task created successfully', $createdTask, [], 201);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            if ($resourceId === null || $resourceId === '') {
                sendJsonResponse(false, 'Task ID is required.', [], ['id' => 'Task ID is required.'], 400);
            }

            if (!Task::validateId($resourceId)) {
                sendJsonResponse(false, 'Task ID must be a positive integer.', [], ['id' => 'Invalid task ID.'], 400);
            }

            $payload = readRequestJson();
            $validation = Task::validatePayload($payload, true);

            if (!$validation['valid']) {
                sendJsonResponse(false, 'Validation failed', [], $validation['errors'], 400);
            }

            $updatedTask = TaskController::update($pdo, (int) $resourceId, $validation['data']);
            if (!$updatedTask) {
                sendJsonResponse(false, 'Task not found', [], [], 404);
            }

            sendJsonResponse(true, 'Task updated successfully', $updatedTask, [], 200);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            if ($resourceId === null || $resourceId === '') {
                sendJsonResponse(false, 'Task ID is required.', [], ['id' => 'Task ID is required.'], 400);
            }

            if (!Task::validateId($resourceId)) {
                sendJsonResponse(false, 'Task ID must be a positive integer.', [], ['id' => 'Invalid task ID.'], 400);
            }

            $deleted = TaskController::delete($pdo, (int) $resourceId);
            if (!$deleted) {
                sendJsonResponse(false, 'Task not found', [], [], 404);
            }

            sendJsonResponse(true, 'Task deleted successfully', [], [], 200);
        }

        sendJsonResponse(false, 'Method not allowed', [], [], 405);
    } catch (InvalidArgumentException $e) {
        sendJsonResponse(false, $e->getMessage(), [], [], 400);
    } catch (PDOException $e) {
        sendJsonResponse(false, 'Database error', [], ['database' => 'Unable to process request.'], 500);
    }
}
