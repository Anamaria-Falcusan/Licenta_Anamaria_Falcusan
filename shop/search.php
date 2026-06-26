<?php
require_once __DIR__ . "/../config/init.php";

$q = trim($_GET["q"] ?? "");

$events = [];
$artists = [];

$isSpectator = isset($_SESSION["id_spectator"]);
$isArtist = isset($_SESSION["id_artist"]);
$isAdmin = isset($_SESSION["admin_id"]);

if ($q !== "") {
    $events = $db->getDBResult(
        "SELECT id_eveniment, nume, gen_muzical, locatie, data_eveniment, pret_bilet, bilete_disponibile
         FROM eveniment
         WHERE nume LIKE ? OR gen_muzical LIKE ? OR locatie LIKE ?
         ORDER BY data_eveniment ASC",
        ["%$q%", "%$q%", "%$q%"]
    ) ?: [];

    $artists = $db->getDBResult(
        "SELECT id_artist, nume, gen_muzical, descriere, poza
         FROM artist
         WHERE nume LIKE ? OR gen_muzical LIKE ? OR descriere LIKE ?
         ORDER BY nume ASC",
        ["%$q%", "%$q%", "%$q%"]
    ) ?: [];
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Căutare - MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <style>
        .results-section {
            margin-top: 24px;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 22px;
            margin-top: 16px;
        }

        .result-card {
            padding: 22px;
            border-radius: 22px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 12px 28px rgba(0,0,0,0.18);
        }

        .result-card h3 {
            margin: 0 0 12px;
            color: #fff;
            font-size: 22px;
        }

        .search-top {
            margin-bottom: 24px;
        }

        .search-top form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-top input {
            flex: 1;
            min-width: 260px;
        }

        .artist-avatar-search {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(244,213,141,0.35);
            margin-bottom: 14px;
            background: rgba(255,255,255,0.06);
        }

        .pill {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            color: #dfe7ff;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand">
            <h1>Căutare</h1>
            <p class="muted">Caută evenimente, artiști și genuri muzicale</p>
        </div>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Evenimente</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/artists.php">Artiști</a>

            <?php if ($isSpectator): ?>
                <a class="btn" href="<?= BASE_URL ?>/shop/my_tickets.php">Biletele mele</a>
                <a class="btn" href="<?= BASE_URL ?>/shop/cart.php">🛒 Coș</a>
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

    <div class="card search-top">
        <form method="get" action="<?= BASE_URL ?>/shop/search.php">
            <input type="text" name="q" placeholder="Caută artiști, evenimente, genuri..." value="<?= htmlspecialchars($q) ?>" required>
            <button class="btn primary" type="submit">Caută</button>
        </form>
    </div>

    <?php if ($q === ""): ?>
        <div class="card">
            <p>Introdu un termen de căutare.</p>
        </div>
    <?php else: ?>
        <div class="card">
            <h2>Rezultate pentru „<?= htmlspecialchars($q) ?>”</h2>
            <p class="muted">
                Am găsit <?= count($events) ?> evenimente și <?= count($artists) ?> artiști.
            </p>
        </div>

        <div class="results-section">
            <div class="card">
                <h2>Evenimente</h2>

                <?php if (!$events): ?>
                    <p>Nu am găsit evenimente pentru această căutare.</p>
                <?php else: ?>
                    <div class="results-grid">
                        <?php foreach ($events as $event): ?>
                            <div class="result-card">
                                <h3><?= htmlspecialchars($event["nume"]) ?></h3>
                                <div class="pill">🎵 <?= htmlspecialchars($event["gen_muzical"]) ?></div>
                                <p><strong>Locație:</strong> <?= htmlspecialchars($event["locatie"]) ?></p>
                                <p><strong>Data:</strong> <?= htmlspecialchars($event["data_eveniment"]) ?></p>
                                <p><strong>Preț:</strong> <?= htmlspecialchars($event["pret_bilet"]) ?> lei</p>
                                <p><strong>Bilete disponibile:</strong> <?= (int)$event["bilete_disponibile"] ?></p>

                                <div class="actions">
                                    <a class="btn primary" href="<?= BASE_URL ?>/shop/product_details.php?id=<?= (int)$event["id_eveniment"] ?>">
                                        Vezi detalii
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="results-section">
            <div class="card">
                <h2>Artiști</h2>

                <?php if (!$artists): ?>
                    <p>Nu am găsit artiști pentru această căutare.</p>
                <?php else: ?>
                    <div class="results-grid">
                        <?php foreach ($artists as $artist): ?>
                            <div class="result-card">
                                <?php if (!empty($artist["poza"])): ?>
                                    <img class="artist-avatar-search" src="<?= htmlspecialchars($artist["poza"]) ?>" alt="<?= htmlspecialchars($artist["nume"]) ?>">
                                <?php else: ?>
                                    <div class="artist-avatar-search" style="display:flex;align-items:center;justify-content:center;font-size:28px;">🎵</div>
                                <?php endif; ?>

                                <h3><?= htmlspecialchars($artist["nume"]) ?></h3>
                                <div class="pill">🎤 <?= htmlspecialchars($artist["gen_muzical"] ?: "Nespecificat") ?></div>
                                <p><?= htmlspecialchars(mb_strimwidth($artist["descriere"] ?? "", 0, 150, "...")) ?></p>

                                <div class="actions">
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
    <?php endif; ?>

</div>
</body>
</html>