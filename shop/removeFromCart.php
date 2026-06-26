<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_spectator"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_spectator = (int)$_SESSION["id_spectator"];
$cart_id = (int)($_GET["cart_id"] ?? 0);

if ($cart_id > 0) {
    $rows = $db->getDBResult(
        "SELECT id, id_eveniment, quantity
         FROM tbl_cart
         WHERE id = ? AND id_spectator = ?",
        [$cart_id, $id_spectator]
    );

    if ($rows) {
        $item = $rows[0];
        $id_eveniment = (int)$item["id_eveniment"];
        $quantity = (int)$item["quantity"];

        $db->updateDB(
            "DELETE FROM tbl_cart WHERE id = ? AND id_spectator = ?",
            [$cart_id, $id_spectator]
        );

        $db->updateDB(
            "UPDATE eveniment SET bilete_disponibile = bilete_disponibile + ? WHERE id_eveniment = ?",
            [$quantity, $id_eveniment]
        );
    }
}

header("Location: " . BASE_URL . "/shop/cart.php");
exit;