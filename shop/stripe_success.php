<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_spectator"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}


define("STRIPE_SECRET_KEY", "sk_test_51SY1znCK7SItRuK1tMSTEkPLGuGl9CieuwuKhKurSs0U5yh5a69MVuTw30UKHc5OHA3qYKPpxkl6WtsIkBaXkCPO00gEKvhdtR");

$session_id   = trim($_GET["session_id"] ?? "");
$id_spectator = (int)$_SESSION["id_spectator"];

if ($session_id === "") {
    header("Location: " . BASE_URL . "/shop/index.php");
    exit;
}


$ch = curl_init("https://api.stripe.com/v1/checkout/sessions/" . urlencode($session_id));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ":",
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$session = json_decode($response, true);

if ($httpCode !== 200 || ($session["payment_status"] ?? "") !== "paid") {
    $errMsg = $session["error"]["message"] ?? "Plata nu a putut fi confirmată.";
    die($errMsg . "<br><a href='" . BASE_URL . "/shop/cart.php'>Înapoi la coș</a>");
}


$alreadyDone = $db->getDBResult(
    "SELECT id_bilet FROM bilet WHERE stripe_session_id = ? LIMIT 1",
    [$session_id]
);

if (!$alreadyDone) {
    
    $cartItems = $db->getDBResult(
        "SELECT c.quantity, e.id_eveniment, e.pret_bilet
         FROM tbl_cart c
         JOIN eveniment e ON e.id_eveniment = c.id_eveniment
         WHERE c.id_spectator = ?",
        [$id_spectator]
    );

    foreach ($cartItems as $item) {
        for ($i = 0; $i < (int)$item["quantity"]; $i++) {
            $db->updateDB(
                "INSERT INTO bilet (id_eveniment, id_spectator, pret, stripe_session_id)
                 VALUES (?, ?, ?, ?)",
                [
                    (int)$item["id_eveniment"],
                    $id_spectator,
                    (float)$item["pret_bilet"],
                    $session_id
                ]
            );
        }
    }

    
    $db->updateDB(
        "DELETE FROM tbl_cart WHERE id_spectator = ?",
        [$id_spectator]
    );
}


$userRows = $db->getDBResult(
    "SELECT nume, email FROM spectator WHERE id_spectator = ? LIMIT 1",
    [$id_spectator]
);

$numeSpectator  = $userRows[0]["nume"] ?? "Spectator";
$emailSpectator = trim($userRows[0]["email"] ?? "");


$ticketRows = $db->getDBResult(
    "SELECT b.id_bilet,
            b.pret,
            e.nume AS nume_eveniment,
            e.data_eveniment,
            e.locatie
     FROM bilet b
     JOIN eveniment e ON e.id_eveniment = b.id_eveniment
     WHERE b.id_spectator = ? AND b.stripe_session_id = ?
     ORDER BY b.id_bilet ASC",
    [$id_spectator, $session_id]
);


if ($emailSpectator !== "" && filter_var($emailSpectator, FILTER_VALIDATE_EMAIL) && !empty($ticketRows)) {
    $subject = "Confirmare cumpărare bilete - MusicConnect";

    $headers  = "From: MusicConnect <anamaria.falcusan@gmail.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";

    $rowsHtml = "";
    $total = 0;

    foreach ($ticketRows as $ticket) {
        $idBilet   = (int)$ticket["id_bilet"];
        $eveniment = htmlspecialchars($ticket["nume_eveniment"]);
        $data      = htmlspecialchars($ticket["data_eveniment"]);
        $locatie   = htmlspecialchars($ticket["locatie"]);
        $pret      = number_format((float)$ticket["pret"], 2, ',', '.');

        $rowsHtml .= "
            <tr>
                <td>{$idBilet}</td>
                <td>{$eveniment}</td>
                <td>{$data}</td>
                <td>{$locatie}</td>
                <td>{$pret} RON</td>
            </tr>
        ";

        $total += (float)$ticket["pret"];
    }

    $totalFormatat = number_format($total, 2, ',', '.');

    $message = "
    <html>
    <head>
        <title>Confirmare cumpărare bilete</title>
    </head>
    <body>
        <h2>Bună, " . htmlspecialchars($numeSpectator) . "!</h2>
        <p>Plata ta a fost confirmată cu succes.</p>
        <p>Mai jos găsești detaliile biletelor cumpărate:</p>

        <table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>
            <tr>
                <th>ID bilet</th>
                <th>Eveniment</th>
                <th>Data</th>
                <th>Locație</th>
                <th>Preț</th>
            </tr>
            {$rowsHtml}
        </table>

        <p><strong>Total plătit:</strong> {$totalFormatat} RON</p>
        <p>Biletele sunt salvate și în contul tău, la secțiunea <strong>Biletele mele</strong>.</p>
        <p>Îți mulțumim că folosești MusicConnect.</p>
    </body>
    </html>
    ";

    $message = wordwrap($message, 70);

    @mail($emailSpectator, $subject, $message, $headers);
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Plată reușită - MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>MusicConnect</h1>
        </div>
        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Evenimente</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/my_tickets.php">Biletele mele</a>
            <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="card" style="text-align:center; padding: 50px 30px;">
        <div style="font-size: 72px; margin-bottom: 20px;">🎉</div>
        <h2 style="color: #a8f0c6; margin-bottom: 15px;">Plată reușită!</h2>
        <p style="font-size: 18px; margin-bottom: 25px;">
            Biletele tale au fost înregistrate cu succes.
        </p>
        <p class="muted" style="margin-bottom: 30px;">
            Poți vedea toate biletele tale în secțiunea <strong>Biletele mele</strong>.
        </p>
        <div class="actions" style="justify-content: center;">
            <a class="btn primary" href="<?= BASE_URL ?>/shop/my_tickets.php">Vezi biletele mele</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Înapoi la evenimente</a>
        </div>
    </div>
</div>
</body>
</html>