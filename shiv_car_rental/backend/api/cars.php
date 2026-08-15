<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond(['success'=>false,'message'=>'Method not allowed.'],405);

$stmt = $pdo->query("SELECT id,name,category,price_per_hour,fuel,transmission,seats,image,status FROM cars WHERE status='available' ORDER BY id");
respond(['success'=>true,'cars'=>$stmt->fetchAll()]);
