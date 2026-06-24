<?php
require_once __DIR__ . "/../config/init.php";
unset($_SESSION["id_artist"]);
header("Location: " . BASE_URL . "/artist_auth/login_artist.php");
exit;