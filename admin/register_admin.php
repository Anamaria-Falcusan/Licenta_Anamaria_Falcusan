<?php
require_once __DIR__ . "/../config/init.php";

/**
 * register_admin.php (ORGANIZATOR)
 * - creează cont de organizator în tabela "organizator"
 * - după creare, redirecționează la login
 */

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nume = trim($_POST["nume"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $telefon = trim($_POST["telefon"] ?? "");
  $parola = $_POST["parola"] ?? "";

  if ($nume === "") $errors[] = "Numele este obligatoriu.";
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalid.";
  if ($telefon === "") $errors[] = "Telefon obligatoriu.";
  if (strlen($parola) < 6) $errors[] = "Parola minim 6 caractere.";

  if (!$errors) {
    $exists = $db->getDBResult("SELECT id_organizator FROM organizator WHERE email = ?", [$email]);
    if ($exists) {
      $errors[] = "Există deja un cont cu acest email.";
    } else {
      $hash = password_hash($parola, PASSWORD_DEFAULT);
      $db->updateDB(
        "INSERT INTO organizator (nume, email, telefon, parola) VALUES (?, ?, ?, ?)",
        [$nume, $email, $telefon, $hash]
      );
      header("Location: " . BASE_URL . "/admin/login_admin.php?registered=1");
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Register Organizator</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>

<div class="container">
  <div class="header">
    <div class="brand">
      <h1>MusicConnect</h1>
      <p class="muted">Înregistrare organizator</p>
    </div>
    <div class="nav">
      <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Home</a>
      <a class="btn" href="<?= BASE_URL ?>/admin/login_admin.php">Login</a>
    </div>
  </div>

  <div class="card">
    <h2>Register organizator</h2>
    <p class="muted">Creează cont pentru a adăuga și administra evenimente.</p>

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
      <label>Nume</label>
      <input type="text" name="nume" required>

      <div class="form-row">
        <div>
          <label>Email</label>
          <input type="email" name="email" required>
        </div>
        <div>
          <label>Telefon</label>
          <input type="text" name="telefon" required>
        </div>
      </div>

      <label>Parolă</label>
      <input type="password" name="parola" required>

      <div class="actions">
        <button class="btn primary" type="submit">Creează cont</button>
        <a class="btn" href="<?= BASE_URL ?>/admin/login_admin.php">Am deja cont</a>
      </div>
    </form>
  </div>

  <div class="footer">© <?= date('Y') ?> MusicConnect</div>
</div>

</body>
</html>
