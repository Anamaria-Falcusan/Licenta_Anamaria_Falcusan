<?php
require_once __DIR__ . "/../config/init.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume    = trim($_POST["nume"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $telefon = trim($_POST["telefon"] ?? "");
    $parola  = $_POST["parola"] ?? "";
    $parola2 = $_POST["parola2"] ?? "";

    if ($nume === "")                               $errors[] = "Numele este obligatoriu.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalid.";
    if ($telefon === "")                            $errors[] = "Telefonul este obligatoriu.";
    if (strlen($parola) < 6)                        $errors[] = "Parola trebuie să aibă minim 6 caractere.";
    if ($parola !== $parola2)                       $errors[] = "Parolele nu coincid.";

    if (!$errors) {
        $existing = $db->getDBResult("SELECT id_artist FROM artist WHERE email = ?", [$email]);
        if ($existing) {
            $errors[] = "Există deja un artist cu acest email.";
        } else {
            $hash = password_hash($parola, PASSWORD_DEFAULT);
            $db->updateDB(
                "INSERT INTO artist (nume, email, telefon, parola) VALUES (?, ?, ?, ?)",
                [$nume, $email, $telefon, $hash]
            );
            $newUser = $db->getDBResult("SELECT id_artist FROM artist WHERE email = ?", [$email]);
            unset($_SESSION["id_spectator"], $_SESSION["id_artist"], $_SESSION["admin_id"]);
            $_SESSION["id_artist"] = (int)$newUser[0]["id_artist"];
            header("Location: " . BASE_URL . "/artist/profile_setup.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Creare cont Artist - MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <style>
        .register-wrap { max-width:480px; margin:0 auto; }
        .steps { display:flex; align-items:center; gap:10px; margin-bottom:28px; }
        .step-dot { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0; }
        .step-dot.active { background:linear-gradient(135deg,#a18cd1,#fbc2eb); color:#1f1f1f; }
        .step-dot.inactive { background:rgba(255,255,255,0.12); color:#cfc7ff; }
        .step-line { flex:1; height:2px; background:rgba(255,255,255,0.15); }
        .step-text { font-size:12px; color:#cfc7ff; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>🎸 Înregistrare Artist</h1>
            <p class="muted">Alătură-te comunității MusicConnect</p>
        </div>
        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/auth/register.php">Înapoi</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Home</a>
        </div>
    </div>

    <div class="card register-wrap">
        <div class="steps">
            <div class="step-dot active">1</div>
            <span class="step-text">Cont</span>
            <div class="step-line"></div>
            <div class="step-dot inactive">2</div>
            <span class="step-text">Profil artistic</span>
        </div>

        <?php if ($errors): ?>
            <div class="alert error">
                <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>Nume artist / trupă</label>
            <input type="text" name="nume" value="<?= htmlspecialchars($_POST["nume"] ?? "") ?>" placeholder="ex: Bosquito, Smiley, Luna Amară..." required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" required>

            <label>Telefon</label>
            <input type="text" name="telefon" value="<?= htmlspecialchars($_POST["telefon"] ?? "") ?>" required>

            <label>Parolă <span class="muted">(minim 6 caractere)</span></label>
            <input type="password" name="parola" required>

            <label>Confirmă parola</label>
            <input type="password" name="parola2" required>

            <div class="actions" style="margin-top:22px;">
                <button class="btn primary" type="submit" style="width:100%; padding:12px; font-size:15px;">Continuă →</button>
            </div>
            <p style="text-align:center; margin-top:14px; font-size:13px; color:#cfc7ff;">
                Ai deja cont? <a href="<?= BASE_URL ?>/auth/login_artist.php">Loghează-te</a>
            </p>
        </form>
    </div>
</div>
</body>
</html>
