<?php
require_once __DIR__ . "/../config/init.php";

unset($_SESSION["admin_id"]);

header("Location: " . BASE_URL . "/admin/login_admin.php");
exit;
