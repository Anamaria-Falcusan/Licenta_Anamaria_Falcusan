<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_artist"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_artist = (int)$_SESSION["id_artist"];
$id_eveniment = (int)($_POST["id_eveniment"] ?? $_GET["id_eveniment"] ?? 0);
$mesaj = trim($_POST["mesaj"] ?? "");

if ($id_eveniment <= 0) {
    header("Location: " . BASE_URL . "/shop/index.php");
    exit;
}

$rows = $db->getDBResult(
    "SELECT id_eveniment, nume FROM eveniment WHERE id_eveniment = ?",
    [$id_eveniment]
);

if (!$rows) {
    die("Eveniment inexistent.");
}

$event = $rows[0];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $existing = $db->getDBResult(
        "SELECT id_aplicare FROM aplicare WHERE id_artist = ? AND id_eveniment = ?",
        [$id_artist, $id_eveniment]
    );

    if ($existing) {
        $errors[] = "Ai aplicat deja la acest eveniment.";
    } else {
        $db->updateDB(
            "INSERT INTO aplicare (id_eveniment, id_artist, mesaj, status)
             VALUES (?, ?, ?, 'trimisa')",
            [$id_eveniment, $id_artist, $mesaj]
        );

        header("Location: " . BASE_URL . "/artist/my_applications.php?sent=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Aplicare la eveniment</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>Aplicare la eveniment</h1>
            <p class="muted"><?= htmlspecialchars($event["nume"]) ?></p>
        </div>

        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Evenimente</a>
            <a class="btn" href="<?= BASE_URL ?>/artist/dashboard.php">Dashboard</a>
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
            <input type="hidden" name="id_eveniment" value="<?= (int)$id_eveniment ?>">

            <label>Mesaj pentru organizator</label>
            <textarea name="mesaj" rows="6" placeholder="Spune de ce crezi că ești potrivit pentru acest eveniment"><?= htmlspecialchars($mesaj) ?></textarea>

            <div class="actions">
                <button class="btn primary" type="submit">Trimite aplicarea</button>
                <a class="btn" href="<?= BASE_URL ?>/shop/product_details.php?id=<?= (int)$id_eveniment ?>">Înapoi la eveniment</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>