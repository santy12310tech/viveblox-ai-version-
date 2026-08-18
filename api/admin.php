<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

$adminId = require_admin($pdo);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = trim((string)($_GET['search'] ?? ''));
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = $pdo->prepare('SELECT id, username, display_name, email, role, club_active, coins, status, created_at FROM users WHERE username LIKE ? OR display_name LIKE ? ORDER BY id DESC LIMIT 100');
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $pdo->query('SELECT id, username, display_name, email, role, club_active, coins, status, created_at FROM users ORDER BY id DESC LIMIT 100');
    }
    json_response(['ok'=>true,'users'=>$stmt->fetchAll()]);
}

if ($method === 'PATCH') {
    $data = json_input();
    $targetId = (int)($data['user_id'] ?? 0);
    if ($targetId < 1) json_response(['ok'=>false,'error'=>'Invalid user_id'],400);
    $allowed = [];
    foreach (['display_name','email','role','status','club_active'] as $field) {
        if (array_key_exists($field, $data)) $allowed[$field] = $data[$field];
    }
    if (isset($allowed['role']) && !in_array($allowed['role'], ['user','moderator','admin'], true)) json_response(['ok'=>false,'error'=>'Invalid role'],400);
    if (isset($allowed['status']) && !in_array($allowed['status'], ['online','offline','away'], true)) json_response(['ok'=>false,'error'=>'Invalid status'],400);
    if (isset($allowed['club_active'])) $allowed['club_active'] = $allowed['club_active'] ? 1 : 0;
    if (!$allowed) json_response(['ok'=>false,'error'=>'No editable fields supplied'],400);

    $sets=[]; $values=[];
    foreach ($allowed as $field=>$value) { $sets[] = "`$field` = ?"; $values[]=$value; }
    $values[]=$targetId;
    $pdo->beginTransaction();
    try {
        $stmt=$pdo->prepare('UPDATE users SET '.implode(', ',$sets).' WHERE id = ?');
        $stmt->execute($values);
        $log=$pdo->prepare('INSERT INTO admin_audit_log (admin_user_id,target_user_id,action,details) VALUES (?,?,?,?)');
        $log->execute([$adminId,$targetId,'user_updated',json_encode($allowed,JSON_UNESCAPED_UNICODE)]);
        $pdo->commit();
    } catch (Throwable $e) { $pdo->rollBack(); json_response(['ok'=>false,'error'=>'Update failed'],500); }
    json_response(['ok'=>true]);
}

json_response(['ok'=>false,'error'=>'Method not allowed'],405);
