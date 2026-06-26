<?php
require_once __DIR__ . "/../config/init.php";

$errors = [];
$success = "";

if (isset($_GET["registered"])) {
  $success = "Cont creat cu succes. Te poți autentifica.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");
  $parola = $_POST["parola"] ?? "";

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email invalid.";
  } else {
    $org = $db->getOne("SELECT id_organizator, parola FROM organizator WHERE email = ?", [$email]);
    if (!$org) {
      $errors[] = "Nu există cont cu acest email.";
    } else {
      if (password_verify($parola, $org["parola"])) {
        $_SESSION["admin_id"] = (int)$org["id_organizator"];
        header("Location: " . BASE_URL . "/admin/admin_home.php");
        exit;
      }
      $errors[] = "Parolă greșită.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Login Organizator</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>

<div class="container">
  <div class="header">
    <div class="brand">
      <h1>MusicConnect</h1>
      <p class="muted">Autentificare organizator</p>
    </div>

    <div class="nav">
      <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Home</a>
      <a class="btn" href="<?= BASE_URL ?>/admin/register_admin.php">Register</a>
    </div>
  </div>

  <div class="card">
    <h2>Login organizator</h2>

    <?php if ($success): ?>
      <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="alert error">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post">
      <label>Email</label>
      <input type="email" name="email" required>

      <label>Parolă</label>
      <input type="password" name="parola" required>

      <div class="actions">
        <button class="btn primary" type="submit">Intră</button>
        <a class="btn" href="<?= BASE_URL ?>/admin/register_admin.php">Creează cont</a>
      </div>
    </form>
  </div>

  <div class="footer">© <?= date('Y') ?> MusicConnect</div>
</div>

</body>
</html>
