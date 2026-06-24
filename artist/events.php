<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_artist"])) {
  header("Location: " . BASE_URL . "/auth/login.php");
  exit;
}
$id_artist = (int)$_SESSION["id_artist"];

$events = $db->getDBResult("SELECT id_eveniment, nume, gen_muzical, locatie, data_eveniment FROM eveniment ORDER BY data_eveniment ASC");

// aplicațiile mele (ca să nu pot aplica de 2 ori)
$myApps = $db->getDBResult("SELECT id_eveniment, status FROM aplicare WHERE id_artist = ?", [$id_artist]);
$map = [];
foreach ($myApps as $a) $map[(int)$a["id_eveniment"]] = $a["status"];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Evenimente</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
  <div class="header">
    <div class="brand">
      <h1>Evenimente</h1>
      <p class="muted">Aplică la evenimente.</p>
    </div>
    <div class="nav">
      <a class="btn" href="<?= BASE_URL ?>/artist/dashboard.php">Dashboard</a>
      <a class="btn" href="<?= BASE_URL ?>/artist/my_applications.php">Aplicările mele</a>
      <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
    </div>
  </div>

  <div class="card">
    <?php if (!$events): ?>
      <p>Nu există evenimente.</p>
    <?php else: ?>
      <table>
        <tr>
          <th>Eveniment</th>
          <th>Gen</th>
          <th>Data</th>
          <th>Locație</th>
          <th>Status</th>
          <th></th>
        </tr>
        <?php foreach ($events as $e):
          $idE = (int)$e["id_eveniment"];
          $st = $map[$idE] ?? null;
        ?>
          <tr>
            <td><?= htmlspecialchars($e["nume"]) ?></td>
            <td><span class="badge"><?= htmlspecialchars($e["gen_muzical"]) ?></span></td>
            <td><?= htmlspecialchars($e["data_eveniment"]) ?></td>
            <td><?= htmlspecialchars($e["locatie"]) ?></td>
            <td><?= $st ? htmlspecialchars($st) : "<span class='muted'>neaplicat</span>" ?></td>
            <td>
              <?php if (!$st): ?>
                <a class="btn primary" href="<?= BASE_URL ?>/artist/apply.php?id_eveniment=<?= $idE ?>">Aplică</a>
              <?php else: ?>
                <span class="muted">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>