<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_spectator"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_spectator = (int)$_SESSION["id_spectator"];


if (!empty($_POST["cart_items"])) {

    $cartItems = $db->getDBResult(
        "SELECT c.id, c.quantity, e.id_eveniment, e.pret_bilet
         FROM tbl_cart c
         JOIN eveniment e ON e.id_eveniment = c.id_eveniment
         WHERE c.id_spectator = ?",
        [$id_spectator]
    );

    foreach ($cartItems as $item) {
        for ($i = 0; $i < (int)$item["quantity"]; $i++) {
            $db->updateDB(
                "INSERT INTO bilet (id_eveniment, id_spectator, pret) VALUES (?, ?, ?)",
                [(int)$item["id_eveniment"], $id_spectator, (float)$item["pret_bilet"]]
            );
        }
    }

   
    $db->updateDB("DELETE FROM tbl_cart WHERE id_spectator = ?", [$id_spectator]);

} else {
    
    $id_eveniment = (int)($_POST["id_eveniment"] ?? 0);
    $qty = (int)($_POST["qty"] ?? 1);

    if ($id_eveniment <= 0) {
        header("Location: " . BASE_URL . "/shop/index.php");
        exit;
    }

    if ($qty < 1) $qty = 1;
    if ($qty > 10) $qty = 10;

    $rows = $db->getDBResult(
        "SELECT pret_bilet FROM eveniment WHERE id_eveniment = ?",
        [$id_eveniment]
    );

    if (!$rows) {
        die("Eveniment inexistent.");
    }

    $pret = (float)$rows[0]["pret_bilet"];

    for ($i = 0; $i < $qty; $i++) {
        $db->updateDB(
            "INSERT INTO bilet (id_eveniment, id_spectator, pret) VALUES (?, ?, ?)",
            [$id_eveniment, $id_spectator, $pret]
        );
    }
}

header("Location: " . BASE_URL . "/shop/my_tickets.php?bought=1");
exit;
