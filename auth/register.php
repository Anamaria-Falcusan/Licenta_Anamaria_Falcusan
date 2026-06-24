<?php
require_once __DIR__ . "/../config/init.php";
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Creare cont - MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand">
            <h1>Creare cont</h1>
            <p class="muted">Alege tipul de cont potrivit pentru tine</p>
        </div>
        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Home</a>
            <a class="btn primary" href="<?= BASE_URL ?>/auth/login.php">Login</a>
        </div>
    </div>

    <div class="auth-grid">

        <div class="auth-card">
            <h2>Spectator</h2>
            <p>Vrei să participi la evenimente și să cumperi bilete? Acesta este contul pentru tine.</p>
            <ul style="margin: 12px 0 20px 18px; color: #cfc7ff; font-size:14px; line-height:1.8;">
                <li>Cumpără bilete la evenimente</li>
                <li>Vezi istoricul biletelor tale</li>
                <li>Descoperă artiști noi</li>
            </ul>
            <div class="actions">
                <a class="btn primary" href="<?= BASE_URL ?>/auth/register_spectator.php">Înregistrează-te ca Spectator</a>
            </div>
        </div>

        <div class="auth-card">
            <h2>Artist</h2>
            <p>Ești muzician sau ai o trupă? Creează-ți profilul și aplică la evenimente.</p>
            <ul style="margin: 12px 0 20px 18px; color: #cfc7ff; font-size:14px; line-height:1.8;">
                <li>Profil public vizibil tuturor</li>
                <li>Aplică la evenimente muzicale</li>
                <li>Urmărește statusul aplicărilor</li>
            </ul>
            <div class="actions">
                <a class="btn primary" href="<?= BASE_URL ?>/auth/register_artist.php">Înregistrează-te ca Artist</a>
            </div>
        </div>

        <div class="auth-card">
            <h2>Organizator</h2>
            <p>Organizezi concerte sau festivaluri? Creează și gestionează evenimente muzicale.</p>
            <ul style="margin: 12px 0 20px 18px; color: #cfc7ff; font-size:14px; line-height:1.8;">
                <li>Creează și editează evenimente</li>
                <li>Selectează artiști din aplicări</li>
                <li>Monitorizează vânzările de bilete</li>
            </ul>
            <div class="actions">
                <a class="btn primary" href="<?= BASE_URL ?>/auth/register_organizator.php">Înregistrează-te ca Organizator</a>
            </div>
        </div>

    </div>

</div>
</body>
</html>
