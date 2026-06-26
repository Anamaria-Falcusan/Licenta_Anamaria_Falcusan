<?php

require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_spectator"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}


define("STRIPE_SECRET_KEY",      "sk_test_51000000000000000000000000");   
define("STRIPE_PUBLISHABLE_KEY", "pk_test_51000000000000000000000000");   


$id_spectator = (int)$_SESSION["id_spectator"];
$id_eveniment = (int)($_POST["id_eveniment"] ?? 0);
$qty          = (int)($_POST["qty"] ?? 1);

if ($id_eveniment <= 0) {
    header("Location: " . BASE_URL . "/shop/index.php");
    exit;
}
if ($qty < 1)  $qty = 1;
if ($qty > 10) $qty = 10;


$rows = $db->getDBResult(
    "SELECT id_eveniment, nume, pret_bilet FROM eveniment WHERE id_eveniment = ?",
    [$id_eveniment]
);
if (!$rows) {
    die("Eveniment inexistent.");
}
$event = $rows[0];

$pret_unit_bani = (int)(round((float)$event["pret_bilet"] * 100)); 


$success_url = (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"]
    . BASE_URL . "/shop/stripe_success.php?session_id={CHECKOUT_SESSION_ID}"
    . "&id_eveniment=" . $id_eveniment . "&qty=" . $qty;

$cancel_url = (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"]
    . BASE_URL . "/shop/product_details.php?id=" . $id_eveniment . "&canceled=1";


$postData = http_build_query([
    "payment_method_types[0]"                          => "card",
    "mode"                                              => "payment",
    "line_items[0][price_data][currency]"               => "ron",
    "line_items[0][price_data][unit_amount]"            => $pret_unit_bani,
    "line_items[0][price_data][product_data][name]"     => "Bilet: " . $event["nume"],
    "line_items[0][price_data][product_data][description]" => "Bilet eveniment MusicConnect",
    "line_items[0][quantity]"                           => $qty,
    "success_url"                                       => $success_url,
    "cancel_url"                                        => $cancel_url,
    "metadata[id_spectator]"                            => $id_spectator,
    "metadata[id_eveniment]"                            => $id_eveniment,
    "metadata[qty]"                                     => $qty,
]);

$ch = curl_init("https://api.stripe.com/v1/checkout/sessions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postData,
    CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ":",
    CURLOPT_HTTPHEADER     => ["Content-Type: application/x-www-form-urlencoded"],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($httpCode !== 200 || empty($data["url"])) {
    $errMsg = $data["error"]["message"] ?? "Eroare necunoscută Stripe.";
    die("Eroare Stripe: " . htmlspecialchars($errMsg)
        . "<br><a href='" . BASE_URL . "/shop/product_details.php?id=" . $id_eveniment . "'>Înapoi</a>");
}


header("Location: " . $data["url"]);
exit;
