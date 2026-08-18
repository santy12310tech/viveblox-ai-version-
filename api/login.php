<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_method('POST');
$data = json_input();
$username = trim((string)($data['username'] ?? ''));
$password = (string)($data['password'] ?? '');
if ($username === '' || $password === '') json_response(['ok'=>false,'error'=>'Username and password are required'],400);
$stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) json_response(['ok'=>false,'error'=>'Invalid credentials'],401);
start_vb_session();
session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['id'];
json_response(['ok'=>true,'user'=>['id'=>(int)$user['id'],'username'=>$user['username'],'role'=>$user['role']]]);
