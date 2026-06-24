<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_organizator = (int)$_SESSION["admin_id"];
$id_eveniment = (int)($_GET["id"] ?? 0);

if ($id_eveniment <= 0) {
    header("Location: " . BASE_URL . "/admin/admin_home.php");
    exit;
}

$rows = $db->getDBResult(
    "SELECT * FROM eveniment WHERE id_eveniment = ? AND id_organizator = ?",
    [$id_eveniment, $id_organizator]
);

if (!$rows) {
    die("Eveniment inexistent sau nu ai acces la el.");
}

$event = $rows[0];
$errors = [];
$success = "";

$totalBilete = (int)$event["total_bilete"];
$bileteDisponibile = (int)$event["bilete_disponibile"];
$bileteVandute = max(0, $totalBilete - $bileteDisponibile);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume = trim($_POST["nume"] ?? "");
    $data = trim($_POST["data"] ?? "");
    $locatie = trim($_POST["locatie"] ?? "");
    $descriere = trim($_POST["descriere"] ?? "");
    $gen_muzical = trim($_POST["gen_muzical"] ?? "");
    $pret_bilet = trim($_POST["pret_bilet"] ?? "");
    $total_bilete_nou = (int)($_POST["total_bilete"] ?? 0);

    if ($nume === "") $errors[] = "Numele evenimentului este obligatoriu.";
    if ($data === "") $errors[] = "Data este obligatorie.";
    if ($locatie === "") $errors[] = "Locația este obligatorie.";
    if ($descriere === "") $errors[] = "Descrierea este obligatorie.";
    if ($gen_muzical === "") $errors[] = "Genul muzical este obligatoriu.";
    if ($pret_bilet === "" || !is_numeric($pret_bilet)) $errors[] = "Prețul biletului este invalid.";
    if ($total_bilete_nou < 0) $errors[] = "Numărul total de bilete este invalid.";
    if ($total_bilete_nou < $bileteVandute) {
        $errors[] = "Numărul total de bilete nu poate fi mai mic decât numărul deja rezervat/vândut (" . $bileteVandute . ").";
    }

    if (!$errors) {
        $bilete_disponibile_noi = $total_bilete_nou - $bileteVandute;

        $db->updateDB(
            "UPDATE eveniment
             SET nume = ?, data_eveniment = ?, locatie = ?, descriere = ?, gen_muzical = ?, pret_bilet = ?, total_bilete = ?, bilete_disponibile = ?
             WHERE id_eveniment = ? AND id_organizator = ?",
            [
                $nume,
                $data,
                $locatie,
                $descriere,
                $gen_muzical,
                $pret_bilet,
                $total_bilete_nou,
                $bilete_disponibile_noi,
                $id_eveniment,
                $id_organizator
            ]
        );

        $success = "Evenimentul a fost actualizat.";

        $rows = $db->getDBResult(
            "SELECT * FROM eveniment WHERE id_eveniment = ? AND id_organizator = ?",
            [$id_eveniment, $id_organizator]
        );
        $event = $rows[0];

        $totalBilete = (int)$event["total_bilete"];
        $bileteDisponibile = (int)$event["bilete_disponibile"];
        $bileteVandute = max(0, $totalBilete - $bileteDisponibile);
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Editare eveniment</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>Editare eveniment</h1>
            <p class="muted">Poți modifica informațiile evenimentului</p>
        </div>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/admin/admin_home.php">Înapoi în panou</a>
            <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <p><strong>Total bilete:</strong> <?= $totalBilete ?></p>
        <p><strong>Bilete disponibile:</strong> <?= $bileteDisponibile ?></p>
        <p><strong>Bilete vândute / rezervate:</strong> <?= $bileteVandute ?></p>

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
            <label>Nume eveniment</label>
            <input type="text" name="nume" value="<?= htmlspecialchars($event["nume"]) ?>" required>

            <label>Data</label>
            <input type="datetime-local" name="data"
                   value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($event["data_eveniment"]))) ?>" required>

            <label>Locație</label>
            <input type="text" name="locatie" value="<?= htmlspecialchars($event["locatie"]) ?>" required>

            <label>Gen muzical</label>
            <input type="text" name="gen_muzical" value="<?= htmlspecialchars($event["gen_muzical"]) ?>" required>

            <label>Preț bilet</label>
            <input type="number" step="0.01" name="pret_bilet" value="<?= htmlspecialchars($event["pret_bilet"]) ?>" required>

            <label>Număr total bilete</label>
            <input type="number" name="total_bilete" min="<?= $bileteVandute ?>" value="<?= htmlspecialchars($event["total_bilete"]) ?>" required>

            <label>Descriere</label>
            <textarea name="descriere" rows="6" required><?= htmlspecialchars($event["descriere"]) ?></textarea>

            <div class="actions">
                <button class="btn primary" type="submit">Salvează modificările</button>
                <a class="btn" href="<?= BASE_URL ?>/admin/event_applications.php?id_eveniment=<?= (int)$id_eveniment ?>">Vezi aplicațiile</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>