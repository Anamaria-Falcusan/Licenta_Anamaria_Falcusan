<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_organizator = (int)$_SESSION["admin_id"];
$id_eveniment = (int)($_GET["id_eveniment"] ?? 0);

if ($id_eveniment <= 0) {
    header("Location: " . BASE_URL . "/admin/admin_home.php");
    exit;
}

$eventRows = $db->getDBResult(
    "SELECT id_eveniment, nume, locatie, data_eveniment AS data
     FROM eveniment
     WHERE id_eveniment = ? AND id_organizator = ?",
    [$id_eveniment, $id_organizator]
);

if (!$eventRows) {
    die("Evenimentul nu există sau nu ai acces la el.");
}

$event = $eventRows[0];

$applications = $db->getDBResult(
    "SELECT 
        a.id_aplicare,
        a.mesaj,
        a.status,
        ar.id_artist,
        ar.nume,
        ar.email,
        ar.telefon,
        ar.gen_muzical,
        ar.descriere,
        ar.poza,
        ar.data_debut
     FROM aplicare a
     JOIN artist ar ON ar.id_artist = a.id_artist
     WHERE a.id_eveniment = ?
     ORDER BY a.id_aplicare DESC",
    [$id_eveniment]
);
$updated = $_GET["updated"] ?? "";
$error = $_GET["error"] ?? "";
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Aplicații eveniment</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>Aplicări pentru eveniment</h1>
            <p class="muted"><?= htmlspecialchars($event["nume"]) ?> — <?= htmlspecialchars($event["locatie"]) ?> — <?= htmlspecialchars($event["data"]) ?></p>
        </div>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/admin/admin_home.php">Înapoi</a>
            <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="card">

    <?php if ($updated === "acceptata"): ?>
        <div class="alert success" style="margin-bottom:15px;">
            Aplicarea a fost acceptată.
        </div>
    <?php elseif ($updated === "respinsa"): ?>
        <div class="alert success" style="margin-bottom:15px;">
            Aplicarea a fost respinsă.
        </div>
    <?php elseif ($error === "notfound"): ?>
        <div class="alert error" style="margin-bottom:15px;">
            Aplicarea nu a fost găsită.
        </div>
    <?php endif; ?>

    <?php if (!$applications): ?>
            <p>Nu există aplicații pentru acest eveniment.</p>
        <?php else: ?>

            <?php foreach ($applications as $app): ?>
                <div class="card" style="margin-bottom:18px;">
                    <h3><?= htmlspecialchars($app["nume"]) ?></h3>

                    <?php if (!empty($app["poza"])): ?>
                        <p>
                            <img src="<?= htmlspecialchars($app["poza"]) ?>" alt="Poza artist"
                                 style="max-width:180px; border-radius:12px; margin-bottom:10px;">
                        </p>
                    <?php endif; ?>

                    <p><strong>Email:</strong> <?= htmlspecialchars($app["email"]) ?></p>
                    <p><strong>Telefon:</strong> <?= htmlspecialchars($app["telefon"]) ?></p>
                    <p><strong>Gen muzical:</strong> <?= htmlspecialchars($app["gen_muzical"]) ?></p>
                    <p><strong>Data debut:</strong> <?= htmlspecialchars($app["data_debut"] ?? "-") ?></p>

                    <p><strong>Descriere artist:</strong></p>
                    <p><?= nl2br(htmlspecialchars($app["descriere"])) ?></p>

                    <p><strong>Mesaj pentru eveniment:</strong></p>
                    <p><?= nl2br(htmlspecialchars($app["mesaj"] ?? "")) ?></p>


                    <?php
    $statusCurent = strtolower(trim((string)$app["status"]));
?>

<p>
    <strong>Status curent:</strong>
    <?php if ($statusCurent === "acceptata"): ?>
        <span class="badge" style="background:rgba(76,217,100,0.18); color:#98f5a9; border:1px solid rgba(76,217,100,0.35);">
            Acceptată
        </span>
    <?php elseif ($statusCurent === "respinsa"): ?>
        <span class="badge" style="background:rgba(255,99,132,0.18); color:#ffb3c1; border:1px solid rgba(255,99,132,0.35);">
            Respinsă
        </span>
    <?php else: ?>
        <span class="badge" style="background:rgba(255,215,120,0.18); color:#ffe7a8; border:1px solid rgba(255,215,120,0.35);">
            În așteptare
        </span>
    <?php endif; ?>
</p>

<div class="actions">
    <?php if ($statusCurent !== "acceptata" && $statusCurent !== "respinsa"): ?>
        <a class="btn primary"
           href="<?= BASE_URL ?>/admin/application_update.php?id_aplicare=<?= (int)$app["id_aplicare"] ?>&status=acceptata&id_eveniment=<?= (int)$id_eveniment ?>">
            Acceptă
        </a>

        <a class="btn danger"
           href="<?= BASE_URL ?>/admin/application_update.php?id_aplicare=<?= (int)$app["id_aplicare"] ?>&status=respinsa&id_eveniment=<?= (int)$id_eveniment ?>">
            Respinge
        </a>
    <?php else: ?>
        <span class="muted">Status deja stabilit.</span>
    <?php endif; ?>
</div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>
</body>
</html>