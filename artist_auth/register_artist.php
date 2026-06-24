<?php
require_once __DIR__ . "/../config/init.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume        = trim($_POST["nume"] ?? "");
    $email       = trim($_POST["email"] ?? "");
    $telefon     = trim($_POST["telefon"] ?? "");
    $parola      = $_POST["parola"] ?? "";
    $gen_muzical = trim($_POST["gen_muzical"] ?? "");
    $descriere   = trim($_POST["descriere"] ?? "");
    $data_debut  = trim($_POST["data_debut"] ?? "");
    $poza        = trim($_POST["poza"] ?? "");

    if ($nume === "")                              $errors[] = "Numele este obligatoriu.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalid.";
    if ($telefon === "")                           $errors[] = "Telefonul este obligatoriu.";
    if (strlen($parola) < 6)                       $errors[] = "Parola trebuie să aibă minim 6 caractere.";
    if ($gen_muzical === "")                       $errors[] = "Genul muzical este obligatoriu.";
    if ($descriere === "")                         $errors[] = "Descrierea este obligatorie.";

    if (!$errors) {
        $existing = $db->getDBResult(
            "SELECT id_artist FROM artist WHERE email = ?",
            [$email]
        );

        if ($existing) {
            $errors[] = "Există deja un artist cu acest email.";
        } else {
            $hash           = password_hash($parola, PASSWORD_DEFAULT);
            $data_debut_sql = ($data_debut === "") ? null : $data_debut;
            $poza_sql       = ($poza === "") ? null : $poza;

            $db->updateDB(
                "INSERT INTO artist (nume, email, telefon, gen_muzical, descriere, poza, data_debut, parola)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$nume, $email, $telefon, $gen_muzical, $descriere, $poza_sql, $data_debut_sql, $hash]
            );

            $newUser = $db->getDBResult(
                "SELECT id_artist FROM artist WHERE email = ?",
                [$email]
            );

            unset($_SESSION["id_spectator"], $_SESSION["id_artist"], $_SESSION["admin_id"]);
            $_SESSION["id_artist"] = (int)$newUser[0]["id_artist"];

            header("Location: " . BASE_URL . "/artist/dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Register Artist</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>Înregistrare Artist</h1>
            <p class="muted">Creează-ți profilul de artist pe MusicConnect</p>
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
            <label>Nume artist / trupă</label>
            <input type="text" name="nume" value="<?= htmlspecialchars($_POST["nume"] ?? "") ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" required>

            <label>Telefon</label>
            <input type="text" name="telefon" value="<?= htmlspecialchars($_POST["telefon"] ?? "") ?>" required>

            <label>Parolă <span class="muted">(minim 6 caractere)</span></label>
            <input type="password" name="parola" required>

            <label>Gen muzical</label>
            <input type="text" name="gen_muzical" value="<?= htmlspecialchars($_POST["gen_muzical"] ?? "") ?>" placeholder="ex: Rock, Pop, Jazz..." required>

            <label>Descriere <span class="muted">(prezintă-te organizatorilor)</span></label>
            <textarea name="descriere" rows="6" placeholder="Spune câteva cuvinte despre tine sau trupa ta..." required><?= htmlspecialchars($_POST["descriere"] ?? "") ?></textarea>

            <label>Data debut <span class="muted">(opțional)</span></label>
            <input type="date" name="data_debut" value="<?= htmlspecialchars($_POST["data_debut"] ?? "") ?>">

            <label>Link poză principală <span class="muted">(opțional)</span></label>
            <input type="text" name="poza" value="<?= htmlspecialchars($_POST["poza"] ?? "") ?>" placeholder="https://...">

            <div class="actions">
                <button class="btn primary" type="submit">Creează cont de artist</button>
                <a class="btn" href="<?= BASE_URL ?>/auth/login_artist.php">Am deja cont</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
