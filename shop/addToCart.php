<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_spectator"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_spectator = (int)$_SESSION["id_spectator"];
$id_eveniment = (int)($_POST["id_eveniment"] ?? 0);
$quantity     = (int)($_POST["quantity"] ?? 1);

if ($id_eveniment <= 0) {
    header("Location: " . BASE_URL . "/shop/index.php");
    exit;
}

if ($quantity < 1) {
    $quantity = 1;
}

if ($quantity > 10) {
    $quantity = 10;
}

$rows = $db->getDBResult(
    "SELECT id_eveniment, bilete_disponibile FROM eveniment WHERE id_eveniment = ?",
    [$id_eveniment]
);

if (!$rows) {
    header("Location: " . BASE_URL . "/shop/index.php");
    exit;
}

$event = $rows[0];
$bileteDisponibile = (int)$event["bilete_disponibile"];

if ($bileteDisponibile <= 0) {
    header("Location: " . BASE_URL . "/shop/product_details.php?id=" . $id_eveniment . "&error=nostock");
    exit;
}

if ($quantity > $bileteDisponibile) {
    header("Location: " . BASE_URL . "/shop/product_details.php?id=" . $id_eveniment . "&error=notenough");
    exit;
}

$existing = $db->getDBResult(
    "SELECT id, quantity FROM tbl_cart WHERE id_spectator = ? AND id_eveniment = ?",
    [$id_spectator, $id_eveniment]
);

if ($existing) {
    $newQty = (int)$existing[0]["quantity"] + $quantity;

    if ($newQty > 10) {
        $quantityDeAdaugat = 10 - (int)$existing[0]["quantity"];
        if ($quantityDeAdaugat <= 0) {
            header("Location: " . BASE_URL . "/shop/product_details.php?id=" . $id_eveniment . "&error=maxcart");
            exit;
        }
        $quantity = $quantityDeAdaugat;
        $newQty = 10;
    }

    if ($quantity > $bileteDisponibile) {
        header("Location: " . BASE_URL . "/shop/product_details.php?id=" . $id_eveniment . "&error=notenough");
        exit;
    }

    $db->updateDB(
        "UPDATE tbl_cart SET quantity = ? WHERE id = ?",
        [$newQty, (int)$existing[0]["id"]]
    );
} else {
    $db->updateDB(
        "INSERT INTO tbl_cart (id_spectator, id_eveniment, quantity) VALUES (?, ?, ?)",
        [$id_spectator, $id_eveniment, $quantity]
    );
}

$db->updateDB(
    "UPDATE eveniment SET bilete_disponibile = bilete_disponibile - ? WHERE id_eveniment = ?",
    [$quantity, $id_eveniment]
);

header("Location: " . BASE_URL . "/shop/product_details.php?id=" . $id_eveniment . "&added=1");
exit;