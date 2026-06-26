<?php
require_once __DIR__ . "/../config/init.php";

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "/shop/index.php");
    exit;
}

$rows = $db->getDBResult("SELECT * FROM eveniment WHERE id_eveniment = ?", [$id]);
if (!$rows) {
    die("Eveniment inexistent.");
}

$event = $rows[0];

$artisti = $db->getDBResult(
    "SELECT ar.id_artist, ar.nume
     FROM aplicare a
     JOIN artist ar ON ar.id_artist = a.id_artist
     WHERE a.id_eveniment = ? AND a.status = 'acceptata'
     ORDER BY ar.nume ASC",
    [$id]
);

$isSpectator = isset($_SESSION["id_spectator"]);
$isArtist    = isset($_SESSION["id_artist"]);
$isAdmin     = isset($_SESSION["admin_id"]);

$addedToCart = isset($_GET["added"]);
$error = $_GET["error"] ?? "";

$totalBilete = (int)($event["total_bilete"] ?? 0);
$bileteDisponibile = (int)($event["bilete_disponibile"] ?? 0);
$bileteVandute = max(0, $totalBilete - $bileteDisponibile);
$stocEpuizat = $bileteDisponibile <= 0;
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($event["nume"]) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <style>
        .event-page {
            max-width: 1180px;
            margin: 0 auto;
        }

        .event-hero {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 24px;
            margin-top: 20px;
            align-items: stretch;
        }

        .event-main-card,
        .event-side-card {
            background: rgba(23, 37, 84, 0.78);
            border: 1px solid rgba(255, 215, 120, 0.18);
            border-radius: 22px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28);
            backdrop-filter: blur(10px);
        }

        .event-main-card {
            padding: 28px;
        }

        .event-side-card {
            padding: 22px;
        }

        .event-badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(255,215,130,0.18), rgba(255,255,255,0.08));
            border: 1px solid rgba(255, 215, 120, 0.28);
            color: #ffe7a8;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .event-title {
            margin: 0 0 8px;
            font-size: 34px;
            line-height: 1.15;
            color: #ffffff;
        }

        .event-subtitle {
            margin: 0 0 24px;
            color: rgba(255,255,255,0.78);
            font-size: 15px;
        }

        .event-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-box {
            padding: 16px 18px;
            border-radius: 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .info-label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #c8d2ff;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 16px;
            color: #ffffff;
            font-weight: 600;
        }

        .event-description-box {
            margin-top: 10px;
            padding: 20px;
            border-radius: 18px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
        }

        .event-description-box h3 {
            margin-top: 0;
            margin-bottom: 12px;
            color: #ffe7a8;
        }

        .event-description-box p {
            margin: 0;
            line-height: 1.75;
            color: rgba(255,255,255,0.90);
        }

        .ticket-summary {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin-bottom: 20px;
        }

        .ticket-box {
            padding: 16px;
            border-radius: 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .ticket-box .label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #c8d2ff;
            margin-bottom: 6px;
        }

        .ticket-box .value {
            font-size: 24px;
            font-weight: 800;
            color: #fff;
        }

        .ticket-box.highlight {
            border: 1px solid rgba(255, 215, 120, 0.35);
            background: linear-gradient(135deg, rgba(255,215,120,0.16), rgba(255,255,255,0.05));
        }

        .stock-pill {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .stock-pill.available {
            background: rgba(76, 217, 100, 0.16);
            color: #98f5a9;
            border: 1px solid rgba(76, 217, 100, 0.35);
        }

        .stock-pill.soldout {
            background: rgba(255, 99, 132, 0.16);
            color: #ffb3c1;
            border: 1px solid rgba(255, 99, 132, 0.35);
        }

        .buy-form label {
            display: block;
            margin-bottom: 8px;
            color: #e9eeff;
            font-weight: 600;
        }

        .buy-form input[type="number"],
        .buy-form textarea {
            width: 100%;
            max-width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(8, 15, 40, 0.55);
            color: white;
            outline: none;
            box-sizing: border-box;
        }

        .buy-form textarea {
            resize: vertical;
            min-height: 120px;
        }

        .event-note {
            margin-top: 14px;
            color: rgba(255,255,255,0.72);
            font-size: 14px;
            line-height: 1.6;
        }

        .event-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
        }

        @media (max-width: 900px) {
            .event-hero {
                grid-template-columns: 1fr;
            }

            .event-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="container event-page">

    <div class="header">
        <div class="brand">
            <h1><?= htmlspecialchars($event["nume"]) ?></h1>
            <p class="muted">Detalii eveniment</p>
        </div>

        <?php if ($error === "nostock"): ?>
    <div class="alert danger" style="margin-bottom:15px;">
        Nu mai sunt bilete disponibile pentru acest eveniment.
    </div>
<?php elseif ($error === "notenough"): ?>
    <div class="alert danger" style="margin-bottom:15px;">
        Nu există suficiente bilete disponibile pentru cantitatea selectată.
    </div>
<?php elseif ($error === "maxcart"): ?>
    <div class="alert danger" style="margin-bottom:15px;">
        Ai deja numărul maxim permis de bilete în coș pentru acest eveniment.
    </div>
<?php endif; ?>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Înapoi la evenimente</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/artists.php">Artiști</a>
            <?php if ($isSpectator): ?>
                <a class="btn" href="<?= BASE_URL ?>/shop/cart.php">🛒 Coș</a>
            <?php endif; ?>
            <?php if ($isSpectator || $isArtist || $isAdmin): ?>
                <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
            <?php else: ?>
                <a class="btn" href="<?= BASE_URL ?>/auth/login.php">Login</a>
                <a class="btn primary" href="<?= BASE_URL ?>/auth/register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="event-hero">
        <div class="event-main-card">
            <span class="event-badge">MusicConnect Event</span>
            <h2 class="event-title"><?= htmlspecialchars($event["nume"]) ?></h2>
            <p class="event-subtitle">
                Descoperă toate informațiile importante despre acest eveniment și rezervă rapid biletele disponibile.
            </p>

            <div class="event-info-grid">
                <div class="info-box">
                    <span class="info-label">Gen muzical</span>
                    <span class="info-value"><?= htmlspecialchars($event["gen_muzical"] ?: "Nespecificat") ?></span>
                </div>

                <div class="info-box">
                    <span class="info-label">Locație</span>
                    <span class="info-value"><?= htmlspecialchars($event["locatie"]) ?></span>
                </div>

                <div class="info-box">
                    <span class="info-label">Data evenimentului</span>
                    <span class="info-value"><?= htmlspecialchars($event["data_eveniment"]) ?></span>
                </div>

                <div class="info-box">
                    <span class="info-label">Preț bilet</span>
                    <span class="info-value"><?= htmlspecialchars($event["pret_bilet"]) ?> lei</span>
                </div>
            </div>

            <div class="event-description-box">
            <?php if (!empty($artisti)): ?>
<div class="event-description-box" style="margin-top:20px;">
    <h3>Vor fi prezenți următorii artiști:</h3>

    <ul style="margin:0; padding-left:18px;">
        <?php foreach ($artisti as $artist): ?>
            <li style="margin-bottom:6px;">
                <a href="<?= BASE_URL ?>/shop/artist_details.php?id=<?= (int)$artist["id_artist"] ?>"
                   style="color:white; text-decoration:none; font-weight:600;">
                    <?= htmlspecialchars($artist["nume"]) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<br>
                <h3>Descriere eveniment</h3>
                <p><?= nl2br(htmlspecialchars($event["descriere"])) ?></p>
            </div>
        </div>

        <div class="event-side-card">
            <?php if ($stocEpuizat): ?>
                <span class="stock-pill soldout">Stoc epuizat</span>
            <?php else: ?>
                <span class="stock-pill available">Bilete disponibile acum</span>
            <?php endif; ?>

            <div class="ticket-summary">
                <div class="ticket-box">
                    <span class="label">Total bilete</span>
                    <span class="value"><?= $totalBilete ?></span>
                </div>

                <div class="ticket-box highlight">
                    <span class="label">Bilete rămase</span>
                    <span class="value"><?= $bileteDisponibile ?></span>
                </div>

                <div class="ticket-box">
                    <span class="label">Bilete vândute</span>
                    <span class="value"><?= $bileteVandute ?></span>
                </div>
            </div>

            <?php if ($isSpectator): ?>

                <?php if ($addedToCart): ?>
                    <div class="alert success" style="margin-bottom:15px;">
                        Biletul a fost adăugat în coș!
                        <a href="<?= BASE_URL ?>/shop/cart.php" style="color:#fff; font-weight:bold;">Vezi coșul →</a>
                    </div>
                <?php endif; ?>

                <?php if ($stocEpuizat): ?>
                    <p class="event-note">
                        Din păcate, pentru acest eveniment nu mai sunt bilete disponibile în acest moment.
                    </p>
                <?php else: ?>
                    <h3 style="margin-top:0; color:#ffe7a8;">Adaugă în coș</h3>

                    <form class="buy-form" method="post" action="<?= BASE_URL ?>/shop/addToCart.php">
                        <input type="hidden" name="id_eveniment" value="<?= (int)$event["id_eveniment"] ?>">

                        <label for="quantity">Cantitate bilete</label>
                        <input
                            id="quantity"
                            type="number"
                            name="quantity"
                            min="1"
                            max="<?= max(1, $bileteDisponibile) ?>"
                            value="1"
                            required
                        >

                        <div class="event-actions">
                            <button class="btn primary" type="submit">🛒 Adaugă în coș</button>
                            <a class="btn" href="<?= BASE_URL ?>/shop/cart.php">Vezi coșul</a>
                        </div>
                    </form>

                    <p class="event-note">
                        Poți selecta maximum <?= $bileteDisponibile ?> bilete disponibile pentru acest eveniment.
                    </p>
                <?php endif; ?>

                <?php elseif ($isArtist): ?>
    <h3 style="margin-top:0; color:#ffe7a8;">Aplică la eveniment</h3>

    <form class="buy-form" method="post" action="<?= BASE_URL ?>/artist/apply.php">
        <input type="hidden" name="id_eveniment" value="<?= (int)$event["id_eveniment"] ?>">

        <label for="mesaj">Mesaj pentru organizator</label>
        <textarea id="mesaj" name="mesaj" rows="5"
            placeholder="Spune de ce crezi că ești potrivit pentru acest eveniment"></textarea>

        <div class="event-actions">
            <button class="btn primary" type="submit">Trimite aplicarea</button>
        </div>
    </form>

<?php elseif ($isAdmin): ?>

    <a class="btn primary"
       href="<?= BASE_URL ?>/admin/admin_edit_event.php?id=<?= $event["id_eveniment"] ?>">
        Editează eveniment
    </a>

<?php else: ?>

    <p class="event-note">
        Dacă vrei să cumperi bilete sau să aplici la acest eveniment, trebuie să fii logat.
    </p>
    <div class="event-actions">
        <a class="btn" href="<?= BASE_URL ?>/auth/login.php">Login</a>
        <a class="btn primary" href="<?= BASE_URL ?>/auth/register.php">Register</a>
    </div>

<?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>