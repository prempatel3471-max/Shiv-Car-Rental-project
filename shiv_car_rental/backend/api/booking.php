<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['success'=>false,'message'=>'Method not allowed.'],405);
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) respond(['success'=>false,'message'=>'Invalid booking id.'],422);

$stmt = $pdo->prepare("SELECT b.*, c.name AS car_name, c.price_per_hour FROM bookings b JOIN cars c ON c.id=b.car_id WHERE b.id=? LIMIT 1");
$stmt->execute([$id]);
$booking = $stmt->fetch();
if (!$booking) respond(['success'=>false,'message'=>'Booking not found.'],404);
respond(['success'=>true,'booking'=>$booking]);
