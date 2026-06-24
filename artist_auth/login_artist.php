<?php
require_once __DIR__ . "/../config/init.php";

/**
 * Login Artist
 * Autentifică artistul pe baza email + parolă.
 */

$errors = [];
$success = "";

if (isset($_GET["registered"])) {
  $success = "Cont creat cu succes. Te poți autentifica.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");
  $parola = $_POST["parola"] ?? "";

  $rows = $db->getDBResult("SELECT id_artist, parola FROM artist WHERE email = ?", [$email]);

  if (!$rows) {
    $errors[] = "Nu există artist cu acest email.";
  } else {
    if (password_verify($parola, $rows[0]["parola"])) {
      $_SESSION["id_artist"] = (int)$rows[0]["id_artist"];
      header("Location: " . BASE_URL . "/artist/dashboard.php");
      exit;
    } else {
      $errors[] = "Parolă greșită.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Login Artist</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
  <div class="card">
    <h2>Login artist</h2>

    <?php if ($success): ?>
      <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="alert error">
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post">
      <label>Email</label>
      <input type="email" name="email" required>

      <label>Parolă</label>
      <input type="password" name="parola" required>

      <div class="actions">
        <button class="btn primary" type="submit">Intră</button>
        <a class="btn" href="<?= BASE_URL ?>/artist_auth/register_artist.php">Register</a>
        <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Înapoi la site</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>