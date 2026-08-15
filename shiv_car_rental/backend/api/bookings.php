<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['success'=>false,'message'=>'Method not allowed.'],405);
$data = json_input();

$carName = required_string($data, 'car_name');
$name = required_string($data, 'customer_name');
$phone = required_string($data, 'customer_phone');
$location = required_string($data, 'pickup_location');
$pickup = required_string($data, 'pickup_date');
$pickupTime = required_string($data, 'pickup_time');
$driver = required_string($data, 'driver_option');
$hours = (int)($data['hours'] ?? 0);

if (!preg_match('/^[0-9]{10}$/', $phone)) respond(['success'=>false,'message'=>'Enter a valid 10-digit phone number.'],422);
if (!in_array($driver, ['self','driver'], true)) respond(['success'=>false,'message'=>'Invalid driver option.'],422);
if ($hours < 1 || $hours > 168) respond(['success'=>false,'message'=>'Rental hours must be between 1 and 168 hours.'],422);

$start = DateTime::createFromFormat('Y-m-d H:i', $pickup . ' ' . $pickupTime);
if (!$start || $start->format('Y-m-d H:i') !== $pickup . ' ' . $pickupTime) {
    respond(['success'=>false,'message'=>'Invalid pickup date or time.'],422);
}
if ($start < new DateTime('now')) respond(['success'=>false,'message'=>'Pickup time cannot be in the past.'],422);
$end = clone $start;
$end->modify('+' . $hours . ' hours');

$stmt = $pdo->prepare("SELECT id,name,price_per_hour FROM cars WHERE name=? AND status='available' LIMIT 1");
$stmt->execute([$carName]);
$car = $stmt->fetch();
if (!$car) respond(['success'=>false,'message'=>'Selected car is not available.'],404);

// Any pending/confirmed booking overlapping the requested time reserves the car.
$check = $pdo->prepare("SELECT booking_code FROM bookings WHERE car_id=? AND booking_status IN ('pending_payment','confirmed') AND pickup_datetime < ? AND return_datetime > ? LIMIT 1");
$check->execute([$car['id'], $end->format('Y-m-d H:i:s'), $start->format('Y-m-d H:i:s')]);
$conflict = $check->fetchColumn();
if ($conflict) {
    respond(['success'=>false,'message'=>'This car is not available for the selected time. Please choose another time or car.'],409);
}

$base = $hours * (float)$car['price_per_hour'];
$driverFee = $driver === 'driver' ? 500 * ceil($hours / 24) : 0;
$tax = round(($base + $driverFee) * 0.18, 2);
$total = round($base + $driverFee + $tax, 2);
$bookingCode = 'SHIV-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

$stmt = $pdo->prepare("INSERT INTO bookings (booking_code,car_id,customer_name,customer_phone,pickup_location,pickup_date,return_date,pickup_datetime,return_datetime,driver_option,hours,base_amount,driver_fee,tax_amount,total_amount,booking_status,payment_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending_payment','pending')");
$stmt->execute([$bookingCode,$car['id'],$name,$phone,$location,$start->format('Y-m-d'),$end->format('Y-m-d'),$start->format('Y-m-d H:i:s'),$end->format('Y-m-d H:i:s'),$driver,$hours,$base,$driverFee,$tax,$total]);

respond(['success'=>true,'message'=>'Car is available and the booking has been reserved.','booking'=>[
    'id'=>(int)$pdo->lastInsertId(),'code'=>$bookingCode,'car'=>$car['name'],'hours'=>$hours,
    'pickup_datetime'=>$start->format('Y-m-d H:i:s'),'return_datetime'=>$end->format('Y-m-d H:i:s'),
    'base_amount'=>$base,'driver_fee'=>$driverFee,'tax_amount'=>$tax,'total_amount'=>$total,
    'payment_status'=>'pending','booking_status'=>'pending_payment'
]],201);
