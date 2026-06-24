<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_organizator = (int)$_SESSION["admin_id"];
$id_eveniment = (int)($_GET["id"] ?? 0);

if ($id_eveniment > 0) {
    $db->updateDB(
        "DELETE FROM eveniment WHERE id_eveniment = ? AND id_organizator = ?",
        [$id_eveniment, $id_organizator]
    );
}

header("Location: " . BASE_URL . "/admin/admin_home.php?deleted=1");
exit;