<?php
declare(strict_types=1);
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

$adminId = require_admin($pdo);
require_method('POST');
$data = json_input();
$userId = (int)($data['user_id'] ?? 0);
$amount = (int)($data['amount'] ?? 0);
$reason = trim((string)($data['reason'] ?? ''));
if ($userId < 1 || $amount === 0 || $reason === '') json_response(['ok'=>false,'error'=>'user_id, non-zero amount and reason are required'],400);

$pdo->beginTransaction();
try {
    $stmt=$pdo->prepare('SELECT coins FROM users WHERE id = ? FOR UPDATE');
    $stmt->execute([$userId]);
    $current=$stmt->fetchColumn();
    if ($current === false) { $pdo->rollBack(); json_response(['ok'=>false,'error'=>'User not found'],404); }
    $newCoins=(int)$current+$amount;
    if ($newCoins < 0) { $pdo->rollBack(); json_response(['ok'=>false,'error'=>'Balance cannot become negative'],400); }
    $stmt=$pdo->prepare('UPDATE users SET coins = ? WHERE id = ?');
    $stmt->execute([$newCoins,$userId]);
    $stmt=$pdo->prepare('INSERT INTO coin_transactions (user_id,admin_user_id,amount,reason) VALUES (?,?,?,?)');
    $stmt->execute([$userId,$adminId,$amount,$reason]);
    $stmt=$pdo->prepare('INSERT INTO admin_audit_log (admin_user_id,target_user_id,action,details) VALUES (?,?,?,?)');
    $stmt->execute([$adminId,$userId,'coins_adjusted',json_encode(['amount'=>$amount,'reason'=>$reason],JSON_UNESCAPED_UNICODE)]);
    $pdo->commit();
    json_response(['ok'=>true,'coins'=>$newCoins]);
} catch (Throwable $e) {
    $pdo->rollBack();
    json_response(['ok'=>false,'error'=>'Coin update failed'],500);
}
