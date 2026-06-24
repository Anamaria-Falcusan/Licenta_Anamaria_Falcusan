<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_artist"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_artist = (int)$_SESSION["id_artist"];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $gen_muzical = trim($_POST["gen_muzical"] ?? "");
    $descriere   = trim($_POST["descriere"] ?? "");
    $data_debut  = trim($_POST["data_debut"] ?? "");
    $website     = trim($_POST["website"] ?? "");

    if ($gen_muzical === "") $errors[] = "Genul muzical este obligatoriu.";
    if ($descriere === "")   $errors[] = "Descrierea este obligatorie.";

    $poza_sql = null;
    if (!empty($_FILES["poza"]["name"])) {
        $allowed = ["image/jpeg","image/png","image/webp","image/gif"];
        $ftype   = mime_content_type($_FILES["poza"]["tmp_name"]);
        if (!in_array($ftype, $allowed)) {
            $errors[] = "Format neacceptat (jpg, png, webp).";
        } elseif ($_FILES["poza"]["size"] > 5*1024*1024) {
            $errors[] = "Poza depășește 5MB.";
        } else {
            $dir = __DIR__ . "/../uploads/avatars/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $ext      = strtolower(pathinfo($_FILES["poza"]["name"], PATHINFO_EXTENSION));
            $filename = "avatar_{$id_artist}_" . time() . ".$ext";
            move_uploaded_file($_FILES["poza"]["tmp_name"], $dir . $filename);
            $poza_sql = BASE_URL . "/uploads/avatars/" . $filename;
        }
    }

    if (!$errors) {
        $db->updateDB(
            "UPDATE artist SET gen_muzical=?, descriere=?, data_debut=?, website=?" . ($poza_sql ? ", poza=?" : "") . " WHERE id_artist=?",
            $poza_sql
                ? [$gen_muzical, $descriere, ($data_debut ?: null), ($website ?: null), $poza_sql, $id_artist]
                : [$gen_muzical, $descriere, ($data_debut ?: null), ($website ?: null), $id_artist]
        );
        header("Location: " . BASE_URL . "/artist/dashboard.php?welcome=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Completează profilul - MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <style>
        .register-wrap { max-width:520px; margin:0 auto; }
        .steps { display:flex; align-items:center; gap:10px; margin-bottom:28px; }
        .step-dot { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0; }
        .step-dot.done { background:#b9fbc0; color:#1b4332; }
        .step-dot.active { background:linear-gradient(135deg,#a18cd1,#fbc2eb); color:#1f1f1f; }
        .step-line { flex:1; height:2px; background:linear-gradient(90deg,#b9fbc0,#a18cd1); }
        .step-text { font-size:12px; color:#cfc7ff; }
        .avatar-circle { width:110px; height:110px; border-radius:50%; margin:0 auto 10px; display:flex; align-items:center; justify-content:center; font-size:44px; background:linear-gradient(135deg,#2b3c78,#1f2f63); border:3px dashed rgba(255,255,255,0.25); overflow:hidden; cursor:pointer; transition:border-color .3s; }
        .avatar-circle:hover { border-color:#fbc2eb; }
        .avatar-circle img { width:100%; height:100%; object-fit:cover; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="brand">
            <h1>🎸 Completează profilul</h1>
            <p class="muted">Pasul 2 — Informații artistice</p>
        </div>
    </div>

    <div class="card register-wrap">
        <div class="steps">
            <div class="step-dot done">✓</div>
            <span class="step-text">Cont</span>
            <div class="step-line"></div>
            <div class="step-dot active">2</div>
            <span class="step-text">Profil artistic</span>
        </div>

        <?php if ($errors): ?>
            <div class="alert error">
                <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <!-- Avatar picker -->
            <div style="text-align:center; margin-bottom:20px;">
                <label for="pozaInput" style="cursor:pointer; display:inline-block;">
                    <div class="avatar-circle" id="avatarCircle">🎵</div>
                    <p class="muted" style="font-size:13px;">📷 Click pentru a adăuga poza de profil</p>
                    <p class="muted" style="font-size:11px;">JPG, PNG, WebP — max 5MB (opțional)</p>
                </label>
                <input type="file" name="poza" id="pozaInput" accept="image/*" style="display:none;">
            </div>

            <label>Gen muzical *</label>
            <input type="text" name="gen_muzical" value="<?= htmlspecialchars($_POST["gen_muzical"] ?? "") ?>" placeholder="ex: Rock, Pop, Jazz, Hip-Hop, Folk..." required>

            <label>Descriere * <span class="muted">(prezintă-te publicului și organizatorilor)</span></label>
            <textarea name="descriere" rows="5" placeholder="Cine ești, ce fel de muzică faci, ce experiență ai..." required><?= htmlspecialchars($_POST["descriere"] ?? "") ?></textarea>

            <label>Data debut <span class="muted">(opțional)</span></label>
            <input type="date" name="data_debut" value="<?= htmlspecialchars($_POST["data_debut"] ?? "") ?>">

            <label>Website / Link social media <span class="muted">(opțional)</span></label>
            <input type="text" name="website" value="<?= htmlspecialchars($_POST["website"] ?? "") ?>" placeholder="https://instagram.com/...">

            <div class="actions" style="margin-top:22px; gap:10px;">
                <button class="btn primary" type="submit" style="flex:1; padding:12px; font-size:15px;">Finalizează profilul 🎉</button>
                <a class="btn" href="<?= BASE_URL ?>/artist/dashboard.php" style="padding:12px;">Completez mai târziu</a>
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
        const c = document.getElementById('avatarCircle');
        c.innerHTML = '<img src="' + e.target.result + '">';
        c.style.border = '3px solid #fbc2eb';
    };
    reader.readAsDataURL(file);
});
</script>
</body>
</html>
