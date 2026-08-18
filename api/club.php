<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

$id = require_login();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $stmt=$pdo->prepare('SELECT club_active FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    json_response(['ok'=>true,'club_active'=>(bool)$stmt->fetchColumn()]);
}
json_response(['ok'=>false,'error'=>'Method not allowed'],405);
