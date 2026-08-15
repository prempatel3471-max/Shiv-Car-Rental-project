<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['success'=>false,'message'=>'Method not allowed.'],405);
$data = json_input();
$bookingId = (int)($data['booking_id'] ?? 0);
$method = trim((string)($data['payment_method'] ?? ''));

if ($bookingId <= 0) respond(['success'=>false,'message'=>'Invalid booking.'],422);
if ($method !== 'cash') respond(['success'=>false,'message'=>'Only cash payment is available.'],422);

$stmt = $pdo->prepare("SELECT id,booking_code,total_amount,booking_status,payment_status FROM bookings WHERE id=? LIMIT 1");
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();
if (!$booking) respond(['success'=>false,'message'=>'Booking not found.'],404);
if ($booking['payment_status'] === 'paid') respond(['success'=>false,'message'=>'Booking is already paid.'],409);

$transactionId = 'CASH-' . strtoupper(bin2hex(random_bytes(5)));
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO payments (booking_id,payment_method,transaction_id,amount,payment_status,paid_at) VALUES (?,?,?,?, 'pending', NULL)");
    $stmt->execute([$bookingId,$method,$transactionId,$booking['total_amount']]);

    $stmt = $pdo->prepare("UPDATE bookings SET payment_status='pending', booking_status='confirmed' WHERE id=?");
    $stmt->execute([$bookingId]);
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    respond(['success'=>false,'message'=>'Payment could not be recorded.'],500);
}

respond([
    'success'=>true,
    'message'=>'Cash booking confirmed. Payment is due at pickup.',
    'payment'=>[
        'transaction_id'=>$transactionId,
        'booking_code'=>$booking['booking_code'],
        'amount'=>(float)$booking['total_amount'],
        'method'=>$method,
        'status'=>'pending'
    ]
]);
