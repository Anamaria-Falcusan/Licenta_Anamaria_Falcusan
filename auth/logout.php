<?php
require_once __DIR__ . "/../config/init.php";

unset($_SESSION["id_spectator"]);
unset($_SESSION["id_artist"]);
unset($_SESSION["admin_id"]);

header("Location: " . BASE_URL . "/shop/index.php");
exit;