<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . "/DBController.php";


$db = new DBController();

define("BASE_URL", "/music_connect");

define("ORGANIZER_CODE", "ORG2026");

define("ARTIST_CODE", "ART2026");