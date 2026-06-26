<?php
require_once __DIR__ . "/../config/init.php";



$gen = trim($_GET["gen"] ?? "");
if ($gen === "") {
    header("Location: " . BASE_URL . "/shop/index.php");
    exit;
}

$events = $db->getDBResult(
    "SELECT id_eveniment, nume, locatie, data_eveniment, pret_bilet
     FROM eveniment
     WHERE gen_muzical = ?
     ORDER BY data_eveniment ASC",
    [$gen]
);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Evenimente - <?= htmlspecialchars($gen) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>

<div class="container">
  <div class="header">
    <div class="brand">
      <h1>MusicConnect</h1>
      <p class="muted">Evenimente pentru artiști independenți</p>
    </div>

    <div class="nav">
      <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Home</a>
      <a class="btn" href="<?= BASE_URL ?>/shop/my_tickets.php">Biletele mele</a>

      <?php if (isset($_SESSION["id_spectator"])): ?>
        <a class="btn" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
      <?php else: ?>
        <a class="btn" href="<?= BASE_URL ?>/auth/login.php">Login</a>
        <a class="btn primary" href="<?= BASE_URL ?>/auth/register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">

<h2>Evenimente (<?= htmlspecialchars($gen) ?>)</h2>

<p>
  <a href="<?= BASE_URL ?>/shop/index.php">← Înapoi la categorii</a> |
  <a href="<?= BASE_URL ?>/shop/my_tickets.php">Biletele mele</a>
</p>

<?php if (!$events): ?>
  <p>Nu există evenimente pentru acest gen.</p>
<?php else: ?>
  <table border="1" cellpadding="8">
    <tr>
      <th>Nume</th>
      <th>Locație</th>
      <th>Data</th>
      <th>Preț bilet</th>
      <th></th>
    </tr>

    <?php foreach ($events as $e): ?>
      <tr>
        <td><?= htmlspecialchars($e["nume"]) ?></td>
        <td><?= htmlspecialchars($e["locatie"]) ?></td>
        <td><?= htmlspecialchars($e["data_eveniment"]) ?></td>
        <td><?= htmlspecialchars($e["pret_bilet"]) ?> lei</td>
        <td>
          <a href="<?= BASE_URL ?>/shop/product_details.php?id=<?= (int)$e["id_eveniment"] ?>">Detalii</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

  </div>
  <div class="footer">© <?= date('Y') ?> MusicConnect</div>
</div>

</body>
</html>