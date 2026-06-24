<?php
require_once __DIR__ . "/../config/init.php";
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand">
            <h1>Autentificare</h1>
            <p class="muted">Alege unde vrei să te conectezi</p>
        </div>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Home</a>
            <a class="btn primary" href="<?= BASE_URL ?>/auth/register.php">Register</a>
        </div>
    </div>

    <div class="auth-grid">

        <div class="auth-card">
            <h2>Spectator</h2>
            <p>Cont pentru utilizatorii care cumpără bilete și urmăresc evenimentele.</p>
            <div class="actions">
                <a class="btn primary" href="<?= BASE_URL ?>/auth/login_spectator.php">Login Spectator</a>
            </div>
        </div>

        <div class="auth-card">
            <h2>Artist</h2>
            <p>Cont pentru artiștii care vor să aplice la evenimente și să își creeze profil.</p>
            <div class="actions">
                <a class="btn primary" href="<?= BASE_URL ?>/auth/login_artist.php">Login Artist</a>
            </div>
        </div>

        <div class="auth-card">
            <h2>Organizator</h2>
            <p>Cont pentru organizatorii care creează și administrează evenimente.</p>
            <div class="actions">
                <a class="btn primary" href="<?= BASE_URL ?>/auth/login_organizator.php">Login Organizator</a>
            </div>
        </div>

    </div>

</div>
</body>
</html>