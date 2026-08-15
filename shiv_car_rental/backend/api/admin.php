<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

function admin_required(): void {
    if (empty($_SESSION['admin_id'])) respond(['success'=>false,'message'=>'Admin login required.'],401);
}
function body(): array { return json_input(); }
function valid_status(string $value, array $allowed): bool { return in_array($value, $allowed, true); }

$action = trim((string)($_GET['action'] ?? ''));

if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['success'=>false,'message'=>'Method not allowed.'],405);
    $data = body();
    $email = strtolower(trim((string)($data['email'] ?? '')));
    $password = (string)($data['password'] ?? '');
    if ($email === '' || $password === '') respond(['success'=>false,'message'=>'Email and password are required.'],422);
    $stmt = $pdo->prepare('SELECT id,name,email,password_hash FROM admins WHERE email=? AND status="active" LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($password, $admin['password_hash'])) respond(['success'=>false,'message'=>'Invalid admin credentials.'],401);
    session_regenerate_id(true);
    $_SESSION['admin_id']=(int)$admin['id']; $_SESSION['admin_name']=$admin['name']; $_SESSION['admin_email']=$admin['email'];
    respond(['success'=>true,'admin'=>['name'=>$admin['name'],'email'=>$admin['email']]]);
}

if ($action === 'logout') {
    $_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); } session_destroy();
    respond(['success'=>true]);
}

if ($action === 'me') {
    admin_required(); respond(['success'=>true,'admin'=>['id'=>(int)$_SESSION['admin_id'],'name'=>$_SESSION['admin_name'],'email'=>$_SESSION['admin_email']]]);
}

admin_required();

switch ($action) {
    case 'stats':
        $stats=[];
        $stats['cars']=(int)$pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
        $stats['available_cars']=(int)$pdo->query("SELECT COUNT(*) FROM cars WHERE status='available'")->fetchColumn();
        $stats['bookings']=(int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
        $stats['pending_payments']=(int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE payment_status='pending'")->fetchColumn();
        $stats['confirmed']=(int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status='confirmed'")->fetchColumn();
        $stats['revenue']=(float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_status='paid'")->fetchColumn();
        $stats['customers']=(int)$pdo->query("SELECT COUNT(DISTINCT customer_phone) FROM bookings")->fetchColumn();
        $recent=$pdo->query("SELECT b.id,b.booking_code,b.customer_name,b.customer_phone,c.name car_name,b.total_amount,b.booking_status,b.payment_status,b.created_at FROM bookings b JOIN cars c ON c.id=b.car_id ORDER BY b.created_at DESC LIMIT 8")->fetchAll();
        respond(['success'=>true,'stats'=>$stats,'recent'=>$recent]);

    case 'cars':
        if ($_SERVER['REQUEST_METHOD']==='GET') { $rows=$pdo->query("SELECT * FROM cars ORDER BY id DESC")->fetchAll(); respond(['success'=>true,'cars'=>$rows]); }
        $data=body(); $op=$data['op']??'';
        if ($op==='delete') { $id=(int)($data['id']??0); if($id<=0) respond(['success'=>false,'message'=>'Invalid car.'],422); $stmt=$pdo->prepare("UPDATE cars SET status='inactive' WHERE id=?"); $stmt->execute([$id]); respond(['success'=>true,'message'=>'Car deactivated.']); }
        $name=trim((string)($data['name']??'')); $category=trim((string)($data['category']??'')); $price=(float)($data['price_per_hour']??0); $fuel=trim((string)($data['fuel']??'Petrol')); $trans=trim((string)($data['transmission']??'Automatic')); $seats=(int)($data['seats']??5); $image=trim((string)($data['image']??'')); $status=trim((string)($data['status']??'available')); $id=(int)($data['id']??0);
        if($name===''||$category===''||$price<=0||$seats<1||!valid_status($status,['available','maintenance','inactive'])) respond(['success'=>false,'message'=>'Enter valid car details.'],422);
        try { if($id>0){$stmt=$pdo->prepare('UPDATE cars SET name=?,category=?,price_per_hour=?,fuel=?,transmission=?,seats=?,image=?,status=? WHERE id=?'); $stmt->execute([$name,$category,$price,$fuel,$trans,$seats,$image,$status,$id]);} else {$stmt=$pdo->prepare('INSERT INTO cars(name,category,price_per_hour,fuel,transmission,seats,image,status) VALUES(?,?,?,?,?,?,?,?)'); $stmt->execute([$name,$category,$price,$fuel,$trans,$seats,$image,$status]);} } catch(Throwable $e){ respond(['success'=>false,'message'=>'Car name may already exist.'],409); }
        respond(['success'=>true,'message'=>$id>0?'Car updated.':'Car added.']);

    case 'bookings':
        if ($_SERVER['REQUEST_METHOD']==='GET') { $rows=$pdo->query("SELECT b.*,c.name car_name,p.transaction_id,p.payment_method FROM bookings b JOIN cars c ON c.id=b.car_id LEFT JOIN payments p ON p.booking_id=b.id ORDER BY b.created_at DESC")->fetchAll(); respond(['success'=>true,'bookings'=>$rows]); }
        $data=body(); $id=(int)($data['id']??0); $status=trim((string)($data['booking_status']??''));
        if($id<=0||!valid_status($status,['pending_payment','confirmed','cancelled','completed'])) respond(['success'=>false,'message'=>'Invalid booking update.'],422);
        $stmt=$pdo->prepare('UPDATE bookings SET booking_status=? WHERE id=?'); $stmt->execute([$status,$id]); respond(['success'=>true,'message'=>'Booking status updated.']);

    case 'payments':
        $rows=$pdo->query("SELECT p.*,b.booking_code,b.customer_name FROM payments p JOIN bookings b ON b.id=p.booking_id ORDER BY p.created_at DESC")->fetchAll(); respond(['success'=>true,'payments'=>$rows]);

    case 'payment_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['success'=>false,'message'=>'Method not allowed.'],405);
        $data=body();
        $id=(int)($data['id']??0);
        $status=trim((string)($data['payment_status']??''));
        if($id<=0 || !valid_status($status,['pending','paid','failed','refunded'])) respond(['success'=>false,'message'=>'Invalid payment status.'],422);
        $stmt=$pdo->prepare("SELECT id,booking_id FROM payments WHERE id=? LIMIT 1");
        $stmt->execute([$id]);
        $payment=$stmt->fetch();
        if(!$payment) respond(['success'=>false,'message'=>'Payment record not found.'],404);
        $pdo->beginTransaction();
        try {
            $paidAt = $status === 'paid' ? date('Y-m-d H:i:s') : null;
            $stmt=$pdo->prepare("UPDATE payments SET payment_status=?, paid_at=? WHERE id=?");
            $stmt->execute([$status,$paidAt,$id]);
            if($status==='paid'){
                $stmt=$pdo->prepare("UPDATE bookings SET payment_status='paid', booking_status='confirmed' WHERE id=?");
                $stmt->execute([$payment['booking_id']]);
            } elseif($status==='refunded'){
                $stmt=$pdo->prepare("UPDATE bookings SET payment_status='refunded' WHERE id=?");
                $stmt->execute([$payment['booking_id']]);
            } elseif($status==='failed'){
                $stmt=$pdo->prepare("UPDATE bookings SET payment_status='failed', booking_status='pending_payment' WHERE id=?");
                $stmt->execute([$payment['booking_id']]);
            } else {
                $stmt=$pdo->prepare("UPDATE bookings SET payment_status='pending' WHERE id=?");
                $stmt->execute([$payment['booking_id']]);
            }
            $pdo->commit();
        } catch(Throwable $e) {
            $pdo->rollBack();
            respond(['success'=>false,'message'=>'Payment status could not be updated.'],500);
        }
        respond(['success'=>true,'message'=>'Payment status updated to '.strtoupper($status).'.']);

    default: respond(['success'=>false,'message'=>'Unknown admin action.'],404);
}
