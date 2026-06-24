<?php
require_once __DIR__ . "/../config/init.php";

// Scoatem doar organizatorul din sesiune (nu afectăm spectator/artist dacă sunt logați în paralel)
unset($_SESSION["admin_id"]);

header("Location: " . BASE_URL . "/admin/login_admin.php");
exit;