<?php
require_once __DIR__ . "/../config/init.php";

$errors = [];
$success = "";

if (isset($_GET["registered"])) {
    $success = "Contul de spectator a fost creat cu succes. Te poți autentifica.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $parola = $_POST["parola"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalid.";
    }

    if (!$errors) {
        unset($_SESSION["id_spectator"], $_SESSION["id_artist"], $_SESSION["admin_id"]);

        $rows = $db->getDBResult(
            "SELECT id_spectator, parola FROM spectator WHERE email = ?",
            [$email]
        );

        if (!$rows) {
            $errors[] = "Nu există spectator cu acest email.";
        } elseif (password_verify($parola, $rows[0]["parola"])) {
            $_SESSION["id_spectator"] = (int)$rows[0]["id_spectator"];
            header("Location: " . BASE_URL . "/shop/index.php");
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
    <title>Login Spectator</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>Login Spectator</h1>
            <p class="muted">Autentificare pentru spectatori</p>
        </div>
        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/auth/login.php">Înapoi</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Home</a>
        </div>
    </div>

    <div class="card">
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert error">
                <ul>
                    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
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
                <a class="btn" href="<?= BASE_URL ?>/auth/register_spectator.php">Nu am cont</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
