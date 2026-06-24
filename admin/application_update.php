<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_aplicare = (int)($_GET["id_aplicare"] ?? 0);
$status = $_GET["status"] ?? "";
$id_eveniment = (int)($_GET["id_eveniment"] ?? 0);
$id_organizator = (int)$_SESSION["admin_id"];

if ($id_aplicare <= 0 || $id_eveniment <= 0) {
    header("Location: " . BASE_URL . "/admin/admin_home.php");
    exit;
}

if (!in_array($status, ["acceptata", "respinsa"], true)) {
    header("Location: " . BASE_URL . "/admin/event_applications.php?id_eveniment=" . $id_eveniment);
    exit;
}

// Verificăm că evenimentul aparține organizatorului logat
$eventRows = $db->getDBResult(
    "SELECT id_eveniment FROM eveniment WHERE id_eveniment = ? AND id_organizator = ?",
    [$id_eveniment, $id_organizator]
);

if (!$eventRows) {
    header("Location: " . BASE_URL . "/admin/admin_home.php");
    exit;
}

// Verificăm că aplicarea există și aparține acelui eveniment
$appRows = $db->getDBResult(
    "SELECT id_aplicare FROM aplicare WHERE id_aplicare = ? AND id_eveniment = ?",
    [$id_aplicare, $id_eveniment]
);

if (!$appRows) {
    header("Location: " . BASE_URL . "/admin/event_applications.php?id_eveniment=" . $id_eveniment . "&error=notfound");
    exit;
}

$ok = $db->updateDB(
    "UPDATE aplicare SET status = ? WHERE id_aplicare = ?",
    [$status, $id_aplicare]
);

if (!$ok) {
    die("Eroare la actualizarea statusului.");
}

header("Location: " . BASE_URL . "/admin/event_applications.php?id_eveniment=" . $id_eveniment . "&updated=" . urlencode($status));
exit;