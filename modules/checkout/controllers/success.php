<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';

$piId = $_GET['payment_intent_id'] ?? '';
if (!$piId) {
    die('Missing payment intent ID');
}

// Confirmar el pago con Stripe
\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
$intent = \Stripe\PaymentIntent::retrieve($piId);
if ($intent->status !== 'succeeded') {
    die('Payment not confirmed');
}

// Recolectar datos del cliente
$shippingName   = $_GET['shipping_name']   ?? '';
$shippingEmail  = $_GET['shipping_email']  ?? '';
$shippingAddr   = $_GET['shipping_address'] ?? '';
$phone          = $_GET['phone']            ?? '';
$lat            = $_GET['latitude']         ?: null;
$lng            = $_GET['longitude']        ?: null;
$last4          = $_GET['card_last4']       ?? '';
$brand          = $_GET['card_brand']       ?? '';
$ipAddress      = $_SERVER['REMOTE_ADDR']   ?? '';

// Calcular total
$total = 0.0;
$orderItems = [];
foreach ($_SESSION['cart'] as $id => $qty) {
    $p = Database::view('v_products', ['id' => $id])[0] ?? null;
    if ($p) {
        $total += $p['price'] * $qty;
        $orderItems[] = [$id, $qty, $p['price']];
    }
}

// Insertar orden
$stmt = Database::conn()->prepare(
    "INSERT INTO orders 
     (user_id, stripe_id, total, status,
      shipping_name, shipping_email, shipping_address, phone,
      card_last4, card_brand, ip_address, latitude, longitude)
     VALUES (?, ?, ?, 'paid', ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->execute([
    $_SESSION['user_id'] ?? null,
    $intent->id,
    $total,
    $shippingName,
    $shippingEmail,
    $shippingAddr,
    $phone,
    $last4,
    $brand,
    $ipAddress,
    $lat,
    $lng
]);
$orderId = Database::conn()->lastInsertId();

// Detalle de líneas
$stmtItem = Database::conn()->prepare(
    "INSERT INTO order_items (order_id, product_id, quantity, price_each) 
     VALUES (?, ?, ?, ?)"
);
foreach ($orderItems as [$prodId, $qty, $price]) {
    $stmtItem->execute([$orderId, $prodId, $qty, $price]);
}

// Vaciar carrito
$_SESSION['cart'] = [];

// Preparar datos del correo electrónico
$emailData = [
    'to'      => $shippingEmail,
    'subject' => 'Order Confirmation',
    'body'    => "Thank you for your order! Order ID: $orderId"
];

// Enviar correo electrónico de confirmación
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/modular-store/modules/email/controllers/send.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($emailData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Verificar respuesta
$response = json_decode($response, true);
if (isset($response['error'])) {
    error_log('Failed to send email: ' . $response['error']);
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container text-center mt-5">
    <h1 class="text-success">Payment succeeded!</h1>
    <p>Order #<?= htmlspecialchars($orderId) ?> is now paid.</p>
    <a class="btn btn-primary" href="/modular-store/modules/catalog/views/list.php">Back to catalog</a>
</div>
</body>
</html>