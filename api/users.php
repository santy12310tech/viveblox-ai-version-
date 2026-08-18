<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
    $id = require_login();
    $stmt = $pdo->prepare('SELECT id, username, display_name, email, role, club_active, coins, avatar_json, status, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) json_response(['ok'=>false,'error'=>'User not found'],404);
    $user['id'] = (int)$user['id'];
    $user['coins'] = (int)$user['coins'];
    $user['club_active'] = (bool)$user['club_active'];
    json_response(['ok'=>true,'user'=>$user]);
}
json_response(['ok'=>false,'error'=>'Method not allowed'],405);
