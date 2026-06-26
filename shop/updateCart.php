<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_spectator"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_spectator = (int)$_SESSION["id_spectator"];
$cart_id = (int)($_POST["cart_id"] ?? 0);
$quantity = (int)($_POST["quantity"] ?? 1);

if ($quantity < 1) $quantity = 1;
if ($quantity > 10) $quantity = 10;

if ($cart_id > 0) {
    $rows = $db->getDBResult(
        "SELECT c.id, c.quantity, c.id_eveniment, e.bilete_disponibile
         FROM tbl_cart c
         JOIN eveniment e ON e.id_eveniment = c.id_eveniment
         WHERE c.id = ? AND c.id_spectator = ?",
        [$cart_id, $id_spectator]
    );

    if ($rows) {
        $item = $rows[0];

        $vecheaCantitate = (int)$item["quantity"];
        $cantitateNoua = $quantity;
        $id_eveniment = (int)$item["id_eveniment"];
        $bileteDisponibile = (int)$item["bilete_disponibile"];

        if ($cantitateNoua > $vecheaCantitate) {
            $diferenta = $cantitateNoua - $vecheaCantitate;

            if ($diferenta <= $bileteDisponibile) {
                $db->updateDB(
                    "UPDATE tbl_cart SET quantity = ? WHERE id = ? AND id_spectator = ?",
                    [$cantitateNoua, $cart_id, $id_spectator]
                );

                $db->updateDB(
                    "UPDATE eveniment SET bilete_disponibile = bilete_disponibile - ? WHERE id_eveniment = ?",
                    [$diferenta, $id_eveniment]
                );
            } else {
                header("Location: " . BASE_URL . "/shop/cart.php?error=notenough");
                exit;
            }
        } elseif ($cantitateNoua < $vecheaCantitate) {
            $diferenta = $vecheaCantitate - $cantitateNoua;

            $db->updateDB(
                "UPDATE tbl_cart SET quantity = ? WHERE id = ? AND id_spectator = ?",
                [$cantitateNoua, $cart_id, $id_spectator]
            );

            $db->updateDB(
                "UPDATE eveniment SET bilete_disponibile = bilete_disponibile + ? WHERE id_eveniment = ?",
                [$diferenta, $id_eveniment]
            );
        }
    }
}

header("Location: " . BASE_URL . "/shop/cart.php");
exit;