<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_organizator = (int)$_SESSION["admin_id"];

$orgRows = $db->getDBResult(
    "SELECT nume FROM organizator WHERE id_organizator = ?",
    [$id_organizator]
);

$organizerName = $orgRows ? $orgRows[0]["nume"] : "Organizator";

$events = $db->getDBResult(
    "SELECT 
        e.id_eveniment,
        e.nume,
        e.gen_muzical,
        e.locatie,
        e.data_eveniment AS data,
        e.pret_bilet,
        COUNT(DISTINCT b.id_bilet) AS bilete_vandute,
        COUNT(DISTINCT a.id_aplicare) AS total_aplicari
     FROM eveniment e
     LEFT JOIN bilet b ON b.id_eveniment = e.id_eveniment
     LEFT JOIN aplicare a ON a.id_eveniment = e.id_eveniment
     WHERE e.id_organizator = ?
     GROUP BY e.id_eveniment, e.nume, e.gen_muzical, e.locatie, e.data_eveniment, e.pret_bilet
     ORDER BY e.data_eveniment DESC",
    [$id_organizator]
);

$added = isset($_GET["added"]);
$deleted = isset($_GET["deleted"]);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Panou Organizator</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>Panou Organizator</h1>
            <p class="muted">Bun venit, <?= htmlspecialchars($organizerName) ?></p>
        </div>

        <div class="nav">
            <a class="btn primary" href="<?= BASE_URL ?>/admin/admin_add_event.php">Adaugă eveniment</a>
            <a class="btn" href="<?= BASE_URL ?>/admin/manage_users.php">👥 Conturi</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Vezi site-ul</a>
            <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <?php if ($added): ?>
            <div class="alert success">Eveniment adăugat cu succes.</div>
        <?php endif; ?>

        <?php if ($deleted): ?>
            <div class="alert success">Eveniment șters cu succes.</div>
        <?php endif; ?>

        <h2>Evenimentele mele</h2>

        <?php if (!$events): ?>
            <p>Nu ai creat încă niciun eveniment.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Nume eveniment</th>
                    <th>Gen</th>
                    <th>Locație</th>
                    <th>Data</th>
                    <th>Preț</th>
                    <th>Bilete vândute</th>
                    <th>Aplicări</th>
                    <th>Acțiuni</th>
                </tr>

                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event["nume"]) ?></td>
                        <td><span class="badge"><?= htmlspecialchars($event["gen_muzical"]) ?></span></td>
                        <td><?= htmlspecialchars($event["locatie"]) ?></td>
                        <td><?= htmlspecialchars($event["data"]) ?></td>
                        <td><?= htmlspecialchars($event["pret_bilet"]) ?> lei</td>
                        <td><?= (int)$event["bilete_vandute"] ?></td>
                        <td><?= (int)$event["total_aplicari"] ?></td>
                        <td>
                            <a class="btn" href="<?= BASE_URL ?>/admin/admin_edit_event.php?id=<?= (int)$event["id_eveniment"] ?>">Edit</a>
                            <a class="btn" href="<?= BASE_URL ?>/admin/event_applications.php?id_eveniment=<?= (int)$event["id_eveniment"] ?>">Aplicări</a>
                            <a class="btn danger"
                               href="<?= BASE_URL ?>/admin/admin_delete_event.php?id=<?= (int)$event["id_eveniment"] ?>"
                               onclick="return confirm('Sigur vrei să ștergi acest eveniment?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>