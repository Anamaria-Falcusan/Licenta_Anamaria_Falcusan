<?php

require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_spectator"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}


define("STRIPE_SECRET_KEY",      "sk_test_51SY1znCK7SItRuK1tMSTEkPLGuGl9CieuwuKhKurSs0U5yh5a69MVuTw30UKHc5OHA3qYKPpxkl6WtsIkBaXkCPO00gEKvhdtR");  
define("STRIPE_PUBLISHABLE_KEY", "pk_test_51SY1znCK7SItRuK1hIPhCgKl2HAvK5OsHM4j3FrgRuMQqiGR9Z4O27zgqmP3S34luzmxkVX4qU2npfHvuaD2LrIZ00vqytNaPZ"); 


$id_spectator = (int)$_SESSION["id_spectator"];


$items = $db->getDBResult(
    "SELECT c.id AS cart_id, c.quantity,
            e.id_eveniment, e.nume, e.pret_bilet
     FROM tbl_cart c
     JOIN eveniment e ON e.id_eveniment = c.id_eveniment
     WHERE c.id_spectator = ?",
    [$id_spectator]
);

if (!$items) {
    header("Location: " . BASE_URL . "/shop/cart.php");
    exit;
}


$postFields = [
    "mode"                   => "payment",
    "payment_method_types[0]" => "card",
    "success_url"            => (isset($_SERVER["HTTPS"]) ? "https" : "http")
                                . "://" . $_SERVER["HTTP_HOST"]
                                . BASE_URL . "/shop/stripe_success.php?session_id={CHECKOUT_SESSION_ID}",
    "cancel_url"             => (isset($_SERVER["HTTPS"]) ? "https" : "http")
                                . "://" . $_SERVER["HTTP_HOST"]
                                . BASE_URL . "/shop/cart.php?canceled=1",
];

foreach ($items as $i => $it) {
    $postFields["line_items[{$i}][price_data][currency]"]                        = "ron";
    $postFields["line_items[{$i}][price_data][unit_amount]"]                     = (int)round((float)$it["pret_bilet"] * 100);
    $postFields["line_items[{$i}][price_data][product_data][name]"]              = "Bilet: " . $it["nume"];
    $postFields["line_items[{$i}][quantity]"]                                    = (int)$it["quantity"];
}


$ch = curl_init("https://api.stripe.com/v1/checkout/sessions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($postFields),
    CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ":",
    CURLOPT_HTTPHEADER     => ["Content-Type: application/x-www-form-urlencoded"],
]);

$response = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($httpCode !== 200 || empty($data["url"])) {
    $errMsg = $data["error"]["message"] ?? "Eroare necunoscută.";
    die("Eroare Stripe: " . htmlspecialchars($errMsg)
        . "<br><a href='" . BASE_URL . "/shop/cart.php'>Înapoi la coș</a>");
}


header("Location: " . $data["url"]);
exit;
