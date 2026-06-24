<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_organizator = (int)$_SESSION["admin_id"];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume = trim($_POST["nume"] ?? "");
    $data = trim($_POST["data"] ?? "");
    $locatie = trim($_POST["locatie"] ?? "");
    $descriere = trim($_POST["descriere"] ?? "");
    $gen_muzical = trim($_POST["gen_muzical"] ?? "");
    $pret_bilet = trim($_POST["pret_bilet"] ?? "");
    $total_bilete = (int)($_POST["total_bilete"] ?? 0);

    if ($nume === "") $errors[] = "Numele evenimentului este obligatoriu.";
    if ($data === "") $errors[] = "Data este obligatorie.";
    if ($locatie === "") $errors[] = "Locația este obligatorie.";
    if ($descriere === "") $errors[] = "Descrierea este obligatorie.";
    if ($gen_muzical === "") $errors[] = "Genul muzical este obligatoriu.";
    if ($pret_bilet === "" || !is_numeric($pret_bilet)) $errors[] = "Prețul biletului este invalid.";
    if ($total_bilete < 0) $errors[] = "Numărul total de bilete este invalid.";

    if (!$errors) {
        $db->updateDB(
            "INSERT INTO eveniment 
            (id_organizator, nume, data_eveniment, locatie, descriere, gen_muzical, pret_bilet, total_bilete, bilete_disponibile)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $id_organizator,
                $nume,
                $data,
                $locatie,
                $descriere,
                $gen_muzical,
                $pret_bilet,
                $total_bilete,
                $total_bilete
            ]
        );

        header("Location: " . BASE_URL . "/admin/admin_home.php?added=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Adaugă eveniment</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>Adaugă eveniment</h1>
            <p class="muted">Completează datele pentru un nou eveniment</p>
        </div>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/admin/admin_home.php">Înapoi în panou</a>
            <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
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
            <label>Nume eveniment</label>
            <input type="text" name="nume" value="<?= htmlspecialchars($_POST["nume"] ?? "") ?>" required>

            <label>Data</label>
            <input type="datetime-local" name="data" value="<?= htmlspecialchars($_POST["data"] ?? "") ?>" required>

            <label>Locație</label>
            <input type="text" name="locatie" value="<?= htmlspecialchars($_POST["locatie"] ?? "") ?>" required>

            <label>Gen muzical</label>
            <input type="text" name="gen_muzical" value="<?= htmlspecialchars($_POST["gen_muzical"] ?? "") ?>" required>

            <label>Preț bilet</label>
            <input type="number" step="0.01" name="pret_bilet" value="<?= htmlspecialchars($_POST["pret_bilet"] ?? "") ?>" required>

            <label>Număr total bilete</label>
            <input type="number" name="total_bilete" min="0" value="<?= htmlspecialchars($_POST["total_bilete"] ?? "100") ?>" required>

            <label>Descriere</label>
            <textarea name="descriere" rows="6" required><?= htmlspecialchars($_POST["descriere"] ?? "") ?></textarea>

            <div class="actions">
                <button class="btn primary" type="submit">Salvează evenimentul</button>
                <a class="btn" href="<?= BASE_URL ?>/admin/admin_home.php">Renunță</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>