<?php
require_once __DIR__ . "/../config/init.php";

$artists = $db->getDBResult(
    "SELECT id_artist, nume, gen_muzical, descriere, poza
     FROM artist
     ORDER BY nume ASC"
);

$isSpectator = isset($_SESSION["id_spectator"]);
$isArtist = isset($_SESSION["id_artist"]);
$isAdmin = isset($_SESSION["admin_id"]);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Artiști</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <style>
        .artists-hero {
            padding: 34px;
            border-radius: 24px;
            margin-bottom: 24px;
            background: linear-gradient(135deg, rgba(28,34,80,0.95), rgba(62,28,88,0.92));
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 20px 45px rgba(0,0,0,0.25);
        }

        .artists-hero h2 {
            margin: 0 0 12px;
            font-size: 34px;
            color: #fff;
        }

        .artists-hero p {
            margin: 0;
            max-width: 760px;
            line-height: 1.7;
            color: rgba(255,255,255,0.85);
        }

        .artists-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 22px;
            margin-top: 18px;
        }

        .artist-card {
            padding: 22px;
            border-radius: 22px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 12px 28px rgba(0,0,0,0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-align: center;
        }

        .artist-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 36px rgba(0,0,0,0.24);
        }

        .artist-avatar {
            width: 110px;
            height: 110px;
            margin: 0 auto 16px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid rgba(255, 215, 120, 0.45);
            box-shadow: 0 10px 24px rgba(0,0,0,0.20);
            background: linear-gradient(135deg, #23336c, #40235c);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
        }

        .artist-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .artist-name {
            margin: 0 0 10px;
            color: #fff;
            font-size: 22px;
        }

        .artist-genre {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 16px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.09);
            color: #dfe7ff;
        }

        .artist-desc {
            min-height: 72px;
            color: rgba(255,255,255,0.88);
            line-height: 1.65;
            font-size: 14px;
            margin-bottom: 18px;
        }

        .artist-actions {
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand">
            <h1>Artiști</h1>
            <p class="muted">Descoperă artiștii din platformă</p>
        </div>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Evenimente</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/artists.php">Artiști</a>

            <?php if ($isSpectator): ?>
                <a class="btn" href="<?= BASE_URL ?>/shop/my_tickets.php">Biletele mele</a>
            <?php endif; ?>

            <?php if ($isArtist): ?>
                <a class="btn" href="<?= BASE_URL ?>/artist/dashboard.php">Dashboard Artist</a>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <a class="btn" href="<?= BASE_URL ?>/admin/admin_home.php">Panou Organizator</a>
            <?php endif; ?>

            <?php if ($isSpectator || $isArtist || $isAdmin): ?>
                <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
            <?php else: ?>
                <a class="btn primary" href="<?= BASE_URL ?>/auth/login.php">Login</a>
                <a class="btn primary" href="<?= BASE_URL ?>/auth/register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="artists-hero">
        <h2>Artiști în MusicConnect</h2>
        <p>
            Explorează profilele artiștilor, vezi genul lor muzical și descoperă rapid cine s-ar potrivi pentru următorul tău eveniment.
        </p>
    </div>

    <div class="card">
        <?php if (!$artists): ?>
            <p>Momentan nu există artiști în platformă.</p>
        <?php else: ?>
            <div class="artists-grid">
                <?php foreach ($artists as $artist): ?>
                    <div class="artist-card">
                        <div class="artist-avatar">
                            <?php if (!empty($artist["poza"])): ?>
                                <img src="<?= htmlspecialchars($artist["poza"]) ?>" alt="<?= htmlspecialchars($artist["nume"]) ?>">
                            <?php else: ?>
                                🎵
                            <?php endif; ?>
                        </div>

                        <h3 class="artist-name"><?= htmlspecialchars($artist["nume"]) ?></h3>

                        <div class="artist-genre">
                            <?= htmlspecialchars($artist["gen_muzical"] ?: "Gen nespecificat") ?>
                        </div>

                        <div class="artist-desc">
                            <?= htmlspecialchars(mb_strimwidth($artist["descriere"] ?? "", 0, 140, "...")) ?>
                        </div>

                        <div class="artist-actions">
                            <a class="btn primary" href="<?= BASE_URL ?>/shop/artist_details.php?id=<?= (int)$artist["id_artist"] ?>">
                                Vezi profil
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>