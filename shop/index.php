<?php
require_once __DIR__ . "/../config/init.php";

$gen = trim($_GET["gen"] ?? "");

if ($gen !== "") {
    $events = $db->getDBResult(
        "SELECT id_eveniment, nume, gen_muzical, locatie, data_eveniment, pret_bilet, bilete_disponibile
         FROM eveniment
         WHERE gen_muzical LIKE ?
         ORDER BY data_eveniment ASC",
        ["%$gen%"]
    );
} else {
    $events = $db->getDBResult(
        "SELECT id_eveniment, nume, gen_muzical, locatie, data_eveniment, pret_bilet, bilete_disponibile
         FROM eveniment
         ORDER BY data_eveniment ASC"
    );
}

$isSpectator = isset($_SESSION["id_spectator"]);
$isArtist = isset($_SESSION["id_artist"]);
$isAdmin = isset($_SESSION["admin_id"]);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css?v=999">
</head>
<body>
    
<div class="container">

    <div class="header">
        <div class="brand">
            <h1>MusicConnect</h1>
            <p class="muted">Platformă pentru conectarea artiștilor cu organizatorii de evenimente</p>
        </div>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php#evenimente">Evenimente</a>
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

    <section class="landing-hero">
        <span class="hero-kicker">Discover • Connect • Perform</span>

        <h2>Locul în care artiștii și organizatorii se întâlnesc pentru evenimente memorabile</h2>

        <p>
            MusicConnect este o platformă dedicată colaborării dintre artiști independenți, organizatori și public.
            Artiștii își pot construi profiluri profesionale, pot aplica la evenimente și își pot promova activitatea,
            în timp ce organizatorii pot publica evenimente, analiza aplicații și găsi mai ușor talentul potrivit.
            Spectatorii pot descoperi evenimente noi, cumpăra bilete și urmări experiențele muzicale preferate într-un singur loc.
        </p>

        <div class="hero-actions">
            <a class="btn primary" href="<?= BASE_URL ?>/shop/index.php#evenimente">Vezi evenimentele</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/artists.php">Explorează artiștii</a>
            <?php if (!$isSpectator && !$isArtist && !$isAdmin): ?>
                <a class="btn" href="<?= BASE_URL ?>/auth/register.php">Creează cont</a>
            <?php endif; ?>
        </div>

        <div class="hero-stats">
            <div class="hero-stat">
                <strong>Artiști</strong>
                <span>Își creează un profil, își prezintă stilul muzical și aplică rapid la oportunități relevante.</span>
            </div>

            <div class="hero-stat">
                <strong>Organizatori</strong>
                <span>Publică evenimente, primesc aplicații și aleg mai ușor artiștii potriviți pentru fiecare context.</span>
            </div>

            <div class="hero-stat">
                <strong>Spectatori</strong>
                <span>Descoperă concerte, urmăresc evenimentele favorite și cumpără bilete direct din platformă.</span>
            </div>
        </div>
    </section>

    <div class="search-section">
        <form method="get" action="<?= BASE_URL ?>/shop/search.php" class="search-bar">
            <input
                type="text"
                name="q"
                placeholder="Caută artiști, evenimente sau genuri muzicale..."
                required
            >
            <button type="submit">Caută</button>
        </form>

        <div class="genre-filters">
            <a href="<?= BASE_URL ?>/shop/index.php#evenimente" class="genre-btn <?= $gen === '' ? 'active' : '' ?>">Toate</a>
            <a href="<?= BASE_URL ?>/shop/index.php?gen=Rock#evenimente" class="genre-btn <?= $gen === 'Rock' ? 'active' : '' ?>">Rock</a>
            <a href="<?= BASE_URL ?>/shop/index.php?gen=Pop#evenimente" class="genre-btn <?= $gen === 'Pop' ? 'active' : '' ?>">Pop</a>
            <a href="<?= BASE_URL ?>/shop/index.php?gen=Jazz#evenimente" class="genre-btn <?= $gen === 'Jazz' ? 'active' : '' ?>">Jazz</a>
            <a href="<?= BASE_URL ?>/shop/index.php?gen=Electronic#evenimente" class="genre-btn <?= $gen === 'Electronic' ? 'active' : '' ?>">Electronic</a>
            <a href="<?= BASE_URL ?>/shop/index.php?gen=Hip-Hop#evenimente" class="genre-btn <?= $gen === 'Hip-Hop' ? 'active' : '' ?>">Hip-Hop</a>
        </div>
    </div>

    <section class="section-intro">
        <h3>Ce oferă MusicConnect</h3>
        <p>
            Platforma este gândită ca un spațiu digital modern pentru industria muzicală: un loc unde promovarea,
            booking-ul și accesul la evenimente se întâlnesc într-o experiență simplă, clară și elegantă.
        </p>

        <div class="features-grid">
            <div class="feature-box">
                <h4>Profiluri artistice</h4>
                <p>Artiștii își pot construi o imagine profesională prin descriere, gen muzical, fotografie și galerie proprie.</p>
            </div>

            <div class="feature-box">
                <h4>Evenimente și aplicații</h4>
                <p>Organizatorii publică evenimente, iar artiștii aplică direct, cu status actualizat permanent pentru fiecare cerere.</p>
            </div>

            <div class="feature-box">
                <h4>Bilete și experiență</h4>
                <p>Spectatorii pot explora rapid evenimentele disponibile, pot adăuga bilete în coș și pot urmări disponibilitatea în timp real.</p>
            </div>
        </div>
    </section>

    <div class="card" id="evenimente">
        <h2>Evenimente disponibile</h2>
        <p class="muted">
            <?php if ($gen !== ""): ?>
                Evenimente filtrate după genul muzical: <strong><?= htmlspecialchars($gen) ?></strong>
            <?php else: ?>
                Descoperă concerte, festivaluri și evenimente live disponibile acum în platformă.
            <?php endif; ?>
        </p>

        <?php if (!$events): ?>
            <p style="margin-top:18px;">Momentan nu există evenimente disponibile.</p>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <?php
                        $bilete = (int)($event["bilete_disponibile"] ?? 0);
                        $ticketClass = "ticket-ok";
                        $ticketText = "Bilete disponibile: " . $bilete;

                        if ($bilete <= 0) {
                            $ticketClass = "ticket-zero";
                            $ticketText = "Stoc epuizat";
                        } elseif ($bilete <= 10) {
                            $ticketClass = "ticket-low";
                            $ticketText = "Puține bilete rămase: " . $bilete;
                        }
                    ?>
                    <div class="event-card">
                        <h3 class="event-title"><?= htmlspecialchars($event["nume"]) ?></h3>

                        <div class="event-tags">
                            <span class="event-pill">🎵 <?= htmlspecialchars($event["gen_muzical"]) ?></span>
                            <span class="event-pill">💳 <?= htmlspecialchars($event["pret_bilet"]) ?> lei</span>
                        </div>

                        <div class="event-meta">
                            <div class="event-meta-row">📍 <strong>Locație:</strong> <?= htmlspecialchars($event["locatie"]) ?></div>
                            <div class="event-meta-row">📅 <strong>Data:</strong> <?= htmlspecialchars($event["data_eveniment"]) ?></div>
                        </div>

                        <span class="ticket-left <?= $ticketClass ?>">
                            <?= $ticketText ?>
                        </span>

                        <div class="event-actions">
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
</body>
</html>