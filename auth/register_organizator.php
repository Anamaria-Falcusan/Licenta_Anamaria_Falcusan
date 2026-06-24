<?php
require_once __DIR__ . "/../config/init.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume    = trim($_POST["nume"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $telefon = trim($_POST["telefon"] ?? "");
    $parola  = $_POST["parola"] ?? "";

    if ($nume === "")                               $errors[] = "Numele este obligatoriu.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalid.";
    if ($telefon === "")                            $errors[] = "Telefonul este obligatoriu.";
    if (strlen($parola) < 6)                        $errors[] = "Parola trebuie să aibă minim 6 caractere.";

    if (!$errors) {
        $existing = $db->getDBResult(
            "SELECT id_organizator FROM organizator WHERE email = ?",
            [$email]
        );

        if ($existing) {
            $errors[] = "Există deja un organizator cu acest email.";
        } else {
            $hash = password_hash($parola, PASSWORD_DEFAULT);

            $db->updateDB(
                "INSERT INTO organizator (nume, email, telefon, parola)
                 VALUES (?, ?, ?, ?)",
                [$nume, $email, $telefon, $hash]
            );

            $newUser = $db->getDBResult(
                "SELECT id_organizator FROM organizator WHERE email = ?",
                [$email]
            );

            unset($_SESSION["id_spectator"], $_SESSION["id_artist"], $_SESSION["admin_id"]);
            $_SESSION["admin_id"] = (int)$newUser[0]["id_organizator"];

            header("Location: " . BASE_URL . "/admin/admin_home.php");
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
            <h1>Înregistrare Organizator</h1>
            <p class="muted">Creează un cont pentru a gestiona evenimente muzicale</p>
        </div>
        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/auth/register.php">Înapoi</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Home</a>
        </div>
    </div>

    <div class="card">
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
            <label>Nume organizație / persoană</label>
            <input type="text" name="nume" value="<?= htmlspecialchars($_POST["nume"] ?? "") ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" required>

            <label>Telefon</label>
            <input type="text" name="telefon" value="<?= htmlspecialchars($_POST["telefon"] ?? "") ?>" required>

            <label>Parolă <span class="muted">(minim 6 caractere)</span></label>
            <input type="password" name="parola" required>

            <div class="actions">
                <button class="btn primary" type="submit">Creează cont de organizator</button>
                <a class="btn" href="<?= BASE_URL ?>/auth/login_organizator.php">Am deja cont</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
