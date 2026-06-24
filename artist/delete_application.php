<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_artist"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_artist   = (int)$_SESSION["id_artist"];
$id_aplicare = (int)($_GET["id_aplicare"] ?? 0);

if ($id_aplicare <= 0) {
    header("Location: " . BASE_URL . "/artist/my_applications.php");
    exit;
}

// șterge doar dacă aplicarea aparține artistului logat
$db->updateDB(
    "DELETE FROM aplicare WHERE id_aplicare = ? AND id_artist = ?",
    [$id_aplicare, $id_artist]
);

header("Location: " . BASE_URL . "/artist/my_applications.php?deleted=1");
exit;