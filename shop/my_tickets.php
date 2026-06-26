<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_spectator"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_spectator = (int)$_SESSION["id_spectator"];

$tickets = $db->getDBResult(
    "SELECT b.id_bilet, b.pret, e.nume, e.gen_muzical, e.locatie, e.data_eveniment
     FROM bilet b
     JOIN eveniment e ON e.id_eveniment = b.id_eveniment
     WHERE b.id_spectator = ?
     ORDER BY b.id_bilet DESC",
    [$id_spectator]
);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Biletele mele</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand">
            <h1>Biletele mele</h1>
            <p class="muted">Toate biletele cumpărate</p>
        </div>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Evenimente</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/artists.php">Artiști</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/cart.php">🛒 Coș</a>
            <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <?php if (isset($_GET["bought"])): ?>
            <div class="alert success">Biletele au fost cumpărate cu succes!</div>
        <?php endif; ?>

        <?php if (!$tickets): ?>
            <p>Nu ai cumpărat încă niciun bilet.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>ID bilet</th>
                    <th>Eveniment</th>
                    <th>Gen</th>
                    <th>Locație</th>
                    <th>Data</th>
                    <th>Preț</th>
                </tr>

                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>#<?= (int)$ticket["id_bilet"] ?></td>
                        <td><?= htmlspecialchars($ticket["nume"]) ?></td>
                        <td><span class="badge"><?= htmlspecialchars($ticket["gen_muzical"]) ?></span></td>
                        <td><?= htmlspecialchars($ticket["locatie"]) ?></td>
                        <td><?= htmlspecialchars($ticket["data_eveniment"]) ?></td>
                        <td><?= htmlspecialchars($ticket["pret"]) ?> lei</td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>