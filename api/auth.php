<?php
declare(strict_types=1);

function require_method(string $method): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        http_response_code(405);
        header('Allow: ' . $method);
        exit;
    }
}

function json_input(): array {
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function start_vb_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function require_login(): int {
    start_vb_session();
    $id = (int)($_SESSION['user_id'] ?? 0);
    if ($id < 1) json_response(['ok' => false, 'error' => 'Authentication required'], 401);
    return $id;
}

function require_admin(PDO $pdo): int {
    $id = require_login();
    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    if (($stmt->fetchColumn() ?: '') !== 'admin') {
        json_response(['ok' => false, 'error' => 'Admin permission required'], 403);
    }
    return $id;
}
