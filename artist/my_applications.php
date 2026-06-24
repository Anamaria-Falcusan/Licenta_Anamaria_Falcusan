<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_artist"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_artist = (int)$_SESSION["id_artist"];
$showSuccess = isset($_GET["sent"]);
$showDeleted = isset($_GET["deleted"]);

$applications = $db->getDBResult(
    "SELECT a.id_aplicare, a.mesaj, a.status,
            e.nume, e.gen_muzical, e.locatie, e.data_eveniment AS data
     FROM aplicare a
     JOIN eveniment e ON e.id_eveniment = a.id_eveniment
     WHERE a.id_artist = ?
     ORDER BY a.id_aplicare DESC",
    [$id_artist]
);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Aplicările mele</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>Aplicările mele</h1>
            <p class="muted">Vezi evenimentele la care ai aplicat</p>
        </div>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/artist/dashboard.php">Dashboard</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Evenimente</a>
            <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <?php if ($showSuccess): ?>
            <div class="alert success">Aplicarea a fost trimisă cu succes.</div>
        <?php endif; ?>

        <?php if ($showDeleted): ?>
    <div class="alert success">Aplicarea a fost ștearsă cu succes.</div>
<?php endif; ?>

        <?php if (!$applications): ?>
            <p>Nu ai trimis încă nicio aplicare.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Eveniment</th>
                    <th>Gen</th>
                    <th>Locație</th>
                    <th>Data</th>
                    <th>Mesaj</th>
                    <th>Status</th>
                    <th>Acțiuni</th>
                </tr>

                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app["nume"]) ?></td>
                        <td><span class="badge"><?= htmlspecialchars($app["gen_muzical"]) ?></span></td>
                        <td><?= htmlspecialchars($app["locatie"]) ?></td>
                        <td><?= htmlspecialchars($app["data"]) ?></td>
                        <td><?= nl2br(htmlspecialchars($app["mesaj"] ?? "")) ?></td>                      
                        <td>
    <?php
        $status = strtolower(trim((string)$app["status"]));
        if ($status === "acceptata") {
            echo '<span class="badge status-acceptata">Acceptată</span>';
        } elseif ($status === "respinsa") {
            echo '<span class="badge status-respinsa">Respinsă</span>';
        } else {
            echo '<span class="badge status-asteptare">În așteptare</span>';
        }
    ?>
</td>
                        <td><a class="btn danger"
       href="<?= BASE_URL ?>/artist/delete_application.php?id_aplicare=<?= (int)$app["id_aplicare"] ?>"
       onclick="return confirm('Sigur vrei să ștergi această aplicare?');">
       Șterge
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