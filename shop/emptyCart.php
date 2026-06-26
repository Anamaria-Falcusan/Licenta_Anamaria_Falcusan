<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_spectator"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_spectator = (int)$_SESSION["id_spectator"];


$items = $db->getDBResult(
    "SELECT id_eveniment, quantity
     FROM tbl_cart
     WHERE id_spectator = ?",
    [$id_spectator]
);

if ($items) {
    foreach ($items as $item) {
        $db->updateDB(
            "UPDATE eveniment
             SET bilete_disponibile = bilete_disponibile + ?
             WHERE id_eveniment = ?",
            [
                (int)$item["quantity"],
                (int)$item["id_eveniment"]
            ]
        );
    }
}

$db->updateDB(
    "DELETE FROM tbl_cart WHERE id_spectator = ?",
    [$id_spectator]
);

header("Location: " . BASE_URL . "/shop/cart.php");
exit;