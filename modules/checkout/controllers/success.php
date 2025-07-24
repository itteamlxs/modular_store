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

// Crear tabla de productos para el correo
$productsTable = '';
$totalAmount = 0;
foreach ($orderItems as [$prodId, $qty, $price]) {
    $product = Database::view('v_products', ['id' => $prodId])[0] ?? null;
    if ($product) {
        $priceFloat = (float)$price;
        $subtotal = $qty * $priceFloat;
        $totalAmount += $subtotal;
        $productsTable .= "
        <tr>
            <td style='padding: 8px; border-bottom: 1px solid #eee;'>{$product['name']}</td>
            <td style='padding: 8px; border-bottom: 1px solid #eee; text-align: center;'>$qty</td>
            <td style='padding: 8px; border-bottom: 1px solid #eee; text-align: right;'>$" . number_format($priceFloat, 2) . "</td>
            <td style='padding: 8px; border-bottom: 1px solid #eee; text-align: right;'>$" . number_format($subtotal, 2) . "</td>
        </tr>";
    }
}

// Crear el cuerpo del correo personalizado
$emailBody = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;'>
    <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>Order Confirmation</h2>
    
    <p>Hello <strong>" . htmlspecialchars($shippingName) . "</strong>,</p>
    
    <p>We confirm that we have received your order. Here are the details:</p>
    
    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
        <p><strong>Order ID:</strong> #$orderId</p>
        <p><strong>Total:</strong> $" . number_format($totalAmount, 2) . "</p>
    </div>
    
    <h3 style='color: #2c3e50; margin-top: 30px;'>Products ordered:</h3>
    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
        <thead>
            <tr style='background: #3498db; color: white;'>
                <th style='padding: 12px; text-align: left;'>Product</th>
                <th style='padding: 12px; text-align: center;'>Qty</th>
                <th style='padding: 12px; text-align: right;'>Price</th>
                <th style='padding: 12px; text-align: right;'>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            $productsTable
        </tbody>
        <tfoot>
            <tr style='background: #ecf0f1; font-weight: bold;'>
                <td colspan='3' style='padding: 12px; text-align: right;'>Total:</td>
                <td style='padding: 12px; text-align: right;'>$" . number_format($totalAmount, 2) . "</td>
            </tr>
        </tfoot>
    </table>
    
    <p style='margin-top: 30px;'>Thank you for your order! We'll process it shortly and send you shipping details.</p>
    
    <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666;'>
        <p>If you have any questions, please contact our support team.</p>
        <p>Best regards,<br><strong>Modular Store Team</strong></p>
    </div>
</div>
";

// Preparar datos del correo electrónico
$emailData = [
    'to'      => $shippingEmail,
    'subject' => "Order Confirmation #$orderId - Modular Store",
    'body'    => $emailBody
];

// Enviar correo electrónico de confirmación
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/modular-store/modules/email/controllers/send.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($emailData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$response = curl_exec($ch);

// Verificar respuesta con mejor manejo de errores
$emailSent = false;
if (curl_errno($ch)) {
    error_log('CURL Error sending email: ' . curl_error($ch));
} else {
    $response = json_decode($response, true);
    if (isset($response['success'])) {
        $emailSent = true;
    } elseif (isset($response['error'])) {
        error_log('Failed to send email: ' . $response['error']);
    }
}
curl_close($ch);

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
    
    <?php if ($emailSent): ?>
        <div class="alert alert-success">
            Confirmation email sent to <?= htmlspecialchars($shippingEmail) ?>
        </div>
    <?php endif; ?>
    
    <a class="btn btn-primary" href="/modular-store/modules/catalog/views/list.php">Back to catalog</a>
</div>
</body>
</html>