<?php

$isSpectator = isset($_SESSION["id_spectator"]);
$isArtist = isset($_SESSION["id_artist"]);
$isAdmin = isset($_SESSION["admin_id"]);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'MusicConnect' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand">
            <h1>MusicConnect</h1>
            <p class="muted">Platformă pentru artiști independenți și evenimente muzicale</p>
        </div>

        <div class="nav">
           
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Evenimente</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/artists.php">Artiști</a>

            
            <?php if ($isSpectator): ?>
                <a class="btn" href="<?= BASE_URL ?>/shop/my_tickets.php">Biletele mele</a>
            <?php endif; ?>

        
            <?php if ($isArtist): ?>
                <a class="btn" href="<?= BASE_URL ?>/artist/dashboard.php">Dashboard Artist</a>
                <a class="btn" href="<?= BASE_URL ?>/artist/my_applications.php">Aplicările mele</a>
            <?php endif; ?>

           
            <?php if ($isAdmin): ?>
                <a class="btn" href="<?= BASE_URL ?>/admin/admin_home.php">Panou Organizator</a>
                <a class="btn primary" href="<?= BASE_URL ?>/admin/admin_add_event.php">Adaugă eveniment</a>
            <?php endif; ?>

            
            <?php if (!$isSpectator && !$isArtist && !$isAdmin): ?>
                <a class="btn" href="<?= BASE_URL ?>/auth/login.php">Login</a>
                <a class="btn primary" href="<?= BASE_URL ?>/auth/register.php">Register</a>
            <?php else: ?>
                <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
            <?php endif; ?>
        </div>
    </div>