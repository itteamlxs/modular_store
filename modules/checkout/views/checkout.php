<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../core/Database.php';

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

$items = [];
foreach ($_SESSION['cart'] as $id => $qty) {
    $p = Database::view('v_products', ['id' => $id])[0] ?? null;
    if ($p) {
        $items[] = [
            'price_data' => [
                'currency'     => 'usd',
                'product_data' => ['name' => $p['name']],
                'unit_amount'  => (int)($p['price'] * 100),
            ],
            'quantity'   => $qty,
        ];
    }
}

$intent = \Stripe\PaymentIntent::create([
    'amount'   => array_sum(array_map(fn($i) => $i['price_data']['unit_amount'] * $i['quantity'], $items)),
    'currency' => 'usd',
    'payment_method_types' => ['card'],
]);

$total = array_sum(array_map(fn($i) => ($i['price_data']['unit_amount'] * $i['quantity']) / 100, $items));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/modular-store/modules/cart/controllers/view.php">← Cart</a>
        <span class="navbar-brand">Checkout</span>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user me-2"></i>Shipping Information</h5>
                </div>
                <div class="card-body">
                    <form id="checkout-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full name</label>
                                <input type="text" class="form-control" name="shipping_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="shipping_email" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="shipping_address" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="getLocation()">
                                    <i class="fas fa-map-marker-alt me-2"></i>Use my current location
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="latitude" id="lat">
                        <input type="hidden" name="longitude" id="lng">
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-credit-card me-2"></i>Payment Information</h5>
                </div>
                <div class="card-body">
                    <div id="card-element" class="form-control mb-3"></div>
                    <div id="card-errors" class="text-danger mb-3" role="alert"></div>
                    <button form="checkout-form" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-lock me-2"></i>Pay $<?= number_format($total, 2) ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-shopping-cart me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?= htmlspecialchars($item['price_data']['product_data']['name']) ?></strong>
                                <br><small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                            </div>
                            <span>$<?= number_format(($item['price_data']['unit_amount'] * $item['quantity']) / 100, 2) ?></span>
                        </div>
                        <hr>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Total:</strong>
                        <strong class="text-success">$<?= number_format($total, 2) ?></strong>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body text-center">
                    <i class="fas fa-shield-alt text-success fa-2x mb-2"></i>
                    <p class="mb-0"><small class="text-muted">Secure payment powered by Stripe</small></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const stripe = Stripe('<?= $_ENV['STRIPE_PUBLIC_KEY'] ?>');
const elements = stripe.elements();
const card = elements.create('card');
card.mount('#card-element');

function getLocation() {
    if (!navigator.geolocation) {
        alert('Geolocation not supported');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        async pos => {
            const { latitude, longitude } = pos.coords;
            document.getElementById('lat').value = latitude;
            document.getElementById('lng').value = longitude;

            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}`;
            const res = await fetch(url);
            const data = await res.json();
            const address = data.display_name || `${latitude}, ${longitude}`;
            document.querySelector('textarea[name="shipping_address"]').value = address;
        },
        err => alert('Could not get location: ' + err.message),
        { enableHighAccuracy: true }
    );
}

document.getElementById('checkout-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = document.getElementById('checkout-form');
    const data = Object.fromEntries(new FormData(form).entries());

    const { paymentMethod, error: pmError } = await stripe.createPaymentMethod({
        type: 'card',
        card: card,
        billing_details: {
            name: data.shipping_name,
            email: data.shipping_email,
            address: { line1: data.shipping_address }
        }
    });
    if (pmError) {
        document.getElementById('card-errors').textContent = pmError.message;
        return;
    }

    const { paymentIntent, error: confirmError } = await stripe.confirmCardPayment(
        '<?= $intent->client_secret ?>',
        { payment_method: paymentMethod.id }
    );
    if (confirmError) {
        document.getElementById('card-errors').textContent = confirmError.message;
        return;
    }

    if (paymentIntent.status === 'succeeded') {
        const cardData = paymentMethod.card;
        const params = new URLSearchParams({
            payment_intent_id: paymentIntent.id,
            ...data,
            card_last4: cardData.last4,
            card_brand: cardData.brand
        });
        location.href = '/modular-store/modules/checkout/controllers/success.php?' + params.toString();
    } else {
        document.getElementById('card-errors').textContent = 'Payment still pending';
    }
});
</script>
</body>
</html>