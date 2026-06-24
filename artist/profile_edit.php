<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_artist"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_artist = (int)$_SESSION["id_artist"];
$errors = [];
$success = "";

$rows = $db->getDBResult("SELECT * FROM artist WHERE id_artist = ?", [$id_artist]);
if (!$rows) die("Artist inexistent.");
$artist = $rows[0];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume        = trim($_POST["nume"] ?? "");
    $telefon     = trim($_POST["telefon"] ?? "");
    $gen_muzical = trim($_POST["gen_muzical"] ?? "");
    $descriere   = trim($_POST["descriere"] ?? "");
    $data_debut  = trim($_POST["data_debut"] ?? "");
    $website     = trim($_POST["website"] ?? "");

    if ($nume === "")        $errors[] = "Numele este obligatoriu.";
    if ($telefon === "")     $errors[] = "Telefonul este obligatoriu.";
    if ($gen_muzical === "") $errors[] = "Genul muzical este obligatoriu.";
    if ($descriere === "")   $errors[] = "Descrierea este obligatorie.";

    $poza_sql = $artist["poza"];
    if (!empty($_FILES["poza"]["name"])) {
        $allowed = ["image/jpeg","image/png","image/webp","image/gif"];
        $ftype = mime_content_type($_FILES["poza"]["tmp_name"]);
        if (!in_array($ftype, $allowed)) {
            $errors[] = "Format neacceptat (jpg, png, webp).";
        } elseif ($_FILES["poza"]["size"] > 5*1024*1024) {
            $errors[] = "Poza depășește 5MB.";
        } else {
            $dir = __DIR__ . "/../uploads/avatars/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $ext = strtolower(pathinfo($_FILES["poza"]["name"], PATHINFO_EXTENSION));
            $name = "avatar_{$id_artist}_" . time() . ".$ext";
            move_uploaded_file($_FILES["poza"]["tmp_name"], $dir . $name);
            $poza_sql = BASE_URL . "/uploads/avatars/" . $name;
        }
    }

    if (!$errors) {
        $db->updateDB(
            "UPDATE artist SET nume=?, telefon=?, gen_muzical=?, descriere=?, data_debut=?, website=?, poza=? WHERE id_artist=?",
            [$nume, $telefon, $gen_muzical, $descriere,
             ($data_debut ?: null), ($website ?: null), $poza_sql, $id_artist]
        );
    
        header("Location: " . BASE_URL . "/artist/dashboard.php?profile_updated=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Editare profil · MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <style>
        .edit-layout { display:grid; grid-template-columns:180px 1fr; gap:30px; align-items:start; }
        .avatar-col { text-align:center; }
        .avatar-ring { width:150px; height:150px; border-radius:50%; overflow:hidden; border:3px solid transparent; background:linear-gradient(#253a75,#1f2f63) padding-box, linear-gradient(135deg,#f9a8d4,#a78bfa) border-box; margin:0 auto 12px; display:flex; align-items:center; justify-content:center; font-size:52px; cursor:pointer; }
        .avatar-ring img { width:100%; height:100%; object-fit:cover; }
        @media(max-width:600px){ .edit-layout{ grid-template-columns:1fr; } .avatar-col{ margin-bottom:10px; } }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand"><h1>✏️ Editare profil</h1></div>
        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/artist/dashboard.php">← Înapoi</a>
            <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <?php if ($success): ?><div class="alert success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert error"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="edit-layout">
                <div class="avatar-col">
                    <label for="pozaInput" style="cursor:pointer;">
                        <div class="avatar-ring" id="avatarRing">
                            <?php if (!empty($artist["poza"])): ?>
                                <img id="avatarImg" src="<?= htmlspecialchars($artist["poza"]) ?>" alt="Avatar">
                            <?php else: ?>
                                <span id="avatarEmoji">🎵</span>
                            <?php endif; ?>
                        </div>
                        <span class="btn" style="font-size:12px; display:block;">📷 Schimbă poza</span>
                        <p class="muted" style="font-size:11px; margin-top:5px;">JPG, PNG — max 5MB</p>
                    </label>
                    <input type="file" name="poza" id="pozaInput" accept="image/*" style="display:none;">
                </div>

                <div>
                    <label>Nume artist / trupă *</label>
                    <input type="text" name="nume" value="<?= htmlspecialchars($artist["nume"]) ?>" required>

                    <label>Telefon *</label>
                    <input type="text" name="telefon" value="<?= htmlspecialchars($artist["telefon"]) ?>" required>

                    <label>Gen muzical *</label>
                    <input type="text" name="gen_muzical" value="<?= htmlspecialchars($artist["gen_muzical"] ?? "") ?>" placeholder="ex: Rock, Pop, Jazz..." required>

                    <label>Data debut <span class="muted">(opțional)</span></label>
                    <input type="date" name="data_debut" value="<?= htmlspecialchars($artist["data_debut"] ?? "") ?>">

                    <label>Website / Social media <span class="muted">(opțional)</span></label>
                    <input type="text" name="website" value="<?= htmlspecialchars($artist["website"] ?? "") ?>" placeholder="https://...">

                    <label>Descriere *</label>
                    <textarea name="descriere" rows="5" required><?= htmlspecialchars($artist["descriere"] ?? "") ?></textarea>

                    <div class="actions" style="margin-top:20px;">
                        <button class="btn primary" type="submit">Salvează modificările</button>
                        <a class="btn" href="<?= BASE_URL ?>/artist/dashboard.php">Anulează</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('pozaInput').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const ring = document.getElementById('avatarRing');
        ring.innerHTML = '<img id="avatarImg" src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
    };
    reader.readAsDataURL(file);
});
</script>
</body>
</html>
