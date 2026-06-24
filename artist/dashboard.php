<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["id_artist"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$id_artist = (int)$_SESSION["id_artist"];
$rows = $db->getDBResult("SELECT * FROM artist WHERE id_artist = ?", [$id_artist]);
if (!$rows) die("Artist inexistent.");
$artist = $rows[0];

// Galerie poze
$poze = $db->getDBResult(
    "SELECT id_poza, url, descriere FROM artist_poze WHERE id_artist = ? ORDER BY id_poza DESC",
    [$id_artist]
);

// Statistici aplicări
$stats = $db->getDBResult(
    "SELECT
        COUNT(*) AS total,
        SUM(status='acceptata') AS acceptate,
        SUM(status='respinsa') AS respinse,
        SUM(status='trimisa') AS in_asteptare
     FROM aplicare WHERE id_artist = ?",
    [$id_artist]
);
$st = $stats[0] ?? ["total"=>0,"acceptate"=>0,"respinse"=>0,"in_asteptare"=>0];

// Handle upload poza galerie
$flashError = "";
$flashOk    = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "upload") {
    if (!empty($_FILES["img"]["name"])) {
        $allowed = ["image/jpeg","image/png","image/webp","image/gif"];
        $ftype   = mime_content_type($_FILES["img"]["tmp_name"]);
        if (!in_array($ftype, $allowed)) {
            $flashError = "Format neacceptat (jpg, png, webp, gif).";
        } elseif ($_FILES["img"]["size"] > 8*1024*1024) {
            $flashError = "Poza depășește 8MB.";
        } else {
            $dir = __DIR__ . "/../uploads/gallery/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $ext  = strtolower(pathinfo($_FILES["img"]["name"], PATHINFO_EXTENSION));
            $name = "gal_{$id_artist}_" . time() . ".$ext";
            move_uploaded_file($_FILES["img"]["tmp_name"], $dir . $name);
            $url  = BASE_URL . "/uploads/gallery/" . $name;
            $desc = trim($_POST["desc"] ?? "");
            $db->updateDB("INSERT INTO artist_poze (id_artist, url, descriere) VALUES (?,?,?)", [$id_artist, $url, $desc]);
            $flashOk = "Poza a fost adăugată!";
            $poze = $db->getDBResult("SELECT id_poza, url, descriere FROM artist_poze WHERE id_artist = ? ORDER BY id_poza DESC", [$id_artist]);
        }
    } else {
        $flashError = "Selectează o poză.";
    }
}

// Ștergere poză
if (isset($_GET["del"]) && is_numeric($_GET["del"])) {
    $db->updateDB("DELETE FROM artist_poze WHERE id_poza=? AND id_artist=?", [(int)$_GET["del"], $id_artist]);
    header("Location: " . BASE_URL . "/artist/dashboard.php");
    exit;
}

$profileIncomplete = empty($artist["gen_muzical"]) || empty($artist["descriere"]);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($artist["nume"]) ?> · MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <style>
        /* ── top nav ── */
        .ig-nav { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:10px; }

        /* ── profile header ── */
        .ig-header { display:flex; gap:50px; align-items:flex-start; margin-bottom:6px; }
        .ig-avatar-col { flex-shrink:0; text-align:center; }
        .ig-avatar {
            width:150px; height:150px; border-radius:50%; object-fit:cover;
            border:3px solid transparent;
            background: linear-gradient(#253a75,#1f2f63) padding-box,
                        linear-gradient(135deg,#f9a8d4,#a78bfa) border-box;
        }
        .ig-avatar-empty {
            width:150px; height:150px; border-radius:50%;
            background:linear-gradient(135deg,#2b3c78,#1f2f63);
            border:3px solid #fbc2eb;
            display:flex; align-items:center; justify-content:center; font-size:56px;
        }
        .ig-info { flex:1; padding-top:8px; }
        .ig-username { font-size:22px; font-weight:300; color:#ffeefc; margin-bottom:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .ig-stats { display:flex; gap:36px; margin-bottom:18px; }
        .ig-stat strong { display:block; font-size:17px; font-weight:700; color:#ffeefc; }
        .ig-stat span { font-size:13px; color:#cfc7ff; }
        .ig-genre { display:inline-block; padding:3px 12px; border-radius:20px; background:linear-gradient(135deg,#fbc2eb,#a6c1ee); color:#1f1f1f; font-weight:600; font-size:12px; margin-bottom:10px; }
        .ig-bio { font-size:14px; color:#e0daff; line-height:1.7; max-width:480px; white-space:pre-line; }
        .ig-link { margin-top:6px; font-size:13px; }
        .ig-actions { display:flex; gap:8px; margin-top:16px; flex-wrap:wrap; }

        /* ── incomplete banner ── */
        .banner-warn { display:flex; align-items:center; gap:14px; background:linear-gradient(135deg,#3d2a5a,#4a1f40); border:1px solid rgba(251,194,235,.4); border-radius:14px; padding:14px 18px; margin-bottom:20px; flex-wrap:wrap; }

        /* ── gallery tabs ── */
        .ig-tab-bar { display:flex; justify-content:center; gap:0; border-top:1px solid rgba(255,255,255,.1); border-bottom:1px solid rgba(255,255,255,.1); margin:0 -1px; }
        .ig-tab { padding:12px 24px; font-size:12px; letter-spacing:1.5px; text-transform:uppercase; color:#cfc7ff; cursor:pointer; border-top:2px solid transparent; transition:.2s; display:flex; align-items:center; gap:6px; }
        .ig-tab.active { color:#ffeefc; border-top-color:#ffeefc; }

        /* ── grid ── */
        .ig-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:3px; }
        .ig-cell { position:relative; aspect-ratio:1; overflow:hidden; background:#1a2550; cursor:pointer; }
        .ig-cell img { width:100%; height:100%; object-fit:cover; transition:transform .3s,filter .3s; display:block; }
        .ig-cell:hover img { transform:scale(1.06); filter:brightness(.65); }
        .ig-cell .cell-actions { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; opacity:0; transition:opacity .3s; }
        .ig-cell:hover .cell-actions { opacity:1; }
        .cell-view { background:rgba(255,255,255,.15); color:#fff; border:none; border-radius:8px; padding:6px 14px; font-size:13px; cursor:pointer; backdrop-filter:blur(4px); }
        .cell-del { background:rgba(220,38,38,.8); color:#fff; border:none; border-radius:8px; padding:6px 14px; font-size:13px; cursor:pointer; text-decoration:none; }

        /* ── upload panel ── */
        .upload-panel { display:none; padding:24px; border-top:1px solid rgba(255,255,255,.08); }
        .upload-panel.open { display:block; }
        .drop-zone { border:2px dashed rgba(255,255,255,.2); border-radius:14px; padding:32px; text-align:center; transition:border-color .3s; cursor:pointer; }
        .drop-zone:hover, .drop-zone.drag { border-color:#fbc2eb; background:rgba(251,194,235,.05); }
        .empty-gallery { text-align:center; padding:50px 20px; color:#cfc7ff; }

        /* ── lightbox ── */
        .lb { display:none; position:fixed; inset:0; background:rgba(0,0,0,.92); z-index:999; align-items:center; justify-content:center; }
        .lb.open { display:flex; }
        .lb img { max-width:92vw; max-height:88vh; border-radius:6px; }
        .lb-close { position:fixed; top:18px; right:24px; font-size:32px; color:#fff; cursor:pointer; line-height:1; z-index:1000; }
        .lb-desc { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); color:#fff; font-size:14px; background:rgba(0,0,0,.5); padding:6px 16px; border-radius:20px; }

        @media(max-width:680px){
            .ig-header { flex-direction:column; align-items:center; text-align:center; gap:20px; }
            .ig-stats { justify-content:center; }
            .ig-actions { justify-content:center; }
            .ig-grid { grid-template-columns:repeat(2,1fr); }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Nav -->
    <div class="ig-nav">
        <div class="brand"><h1>MusicConnect</h1></div>
        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Evenimente</a>
            <a class="btn" href="<?= BASE_URL ?>/artist/my_applications.php">Aplicările mele</a>
         <!--   <a class="btn" href="<?= BASE_URL ?>/shop/artist_details.php?id=<?= $id_artist ?>">Profil public →</a> -->
            <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    </div>

    <?php if (isset($_GET["welcome"])): ?>
        <div class="alert success" style="margin-bottom:20px;">🎉 Bun venit pe MusicConnect! Profilul tău a fost creat.</div>
    <?php endif; ?>

    <?php if (isset($_GET["profile_updated"])): ?>
    <div class="alert success" style="margin-bottom:20px;">✅ Profilul tău a fost actualizat cu succes.</div>
<?php endif; ?>

    <?php if ($profileIncomplete): ?>
        <div class="banner-warn">
            <span style="font-size:26px;">⚠️</span>
            <div style="flex:1;">
                <strong>Profilul tău este incomplet.</strong>
                <p class="muted" style="margin:2px 0 0;">Adaugă genul muzical și o descriere pentru a apărea în lista de artiști.</p>
            </div>
            <a class="btn primary" href="<?= BASE_URL ?>/artist/profile_setup.php">Completează</a>
        </div>
    <?php endif; ?>

    <!-- Profile Header -->
    <div class="card" style="padding:30px; margin-bottom:3px; border-radius:18px 18px 0 0;">
        <div class="ig-header">
            <div class="ig-avatar-col">
                <?php if (!empty($artist["poza"])): ?>
                    <img class="ig-avatar" src="<?= htmlspecialchars($artist["poza"]) ?>" alt="Avatar">
                <?php else: ?>
                    <div class="ig-avatar-empty">🎵</div>
                <?php endif; ?>
            </div>

            <div class="ig-info">
                <div class="ig-username">
                    <?= htmlspecialchars($artist["nume"]) ?>
                    <a class="btn" href="<?= BASE_URL ?>/artist/profile_edit.php" style="font-size:12px; padding:5px 14px;">✏️ Editează profilul</a>
                </div>

                <div class="ig-stats">
                    <div class="ig-stat"><strong><?= count($poze) ?></strong><span>poze</span></div>
                    <div class="ig-stat"><strong><?= (int)$st["total"] ?></strong><span>aplicări</span></div>
                    <div class="ig-stat"><strong><?= (int)$st["acceptate"] ?></strong><span>acceptate</span></div>
                    <div class="ig-stat"><strong><?= (int)$st["in_asteptare"] ?></strong><span>în așteptare</span></div>
                </div>

                <?php if (!empty($artist["gen_muzical"])): ?>
                    <div><span class="ig-genre">🎵 <?= htmlspecialchars($artist["gen_muzical"]) ?></span></div>
                <?php endif; ?>

                <?php if (!empty($artist["descriere"])): ?>
                    <p class="ig-bio"><?= htmlspecialchars($artist["descriere"]) ?></p>
                <?php endif; ?>

                <?php if (!empty($artist["website"])): ?>
                    <p class="ig-link">🔗 <a href="<?= htmlspecialchars($artist["website"]) ?>" target="_blank"><?= htmlspecialchars($artist["website"]) ?></a></p>
                <?php endif; ?>

                <?php if (!empty($artist["data_debut"])): ?>
                    <p class="muted" style="font-size:13px; margin-top:6px;">🎤 Activ din <?= htmlspecialchars($artist["data_debut"]) ?></p>
                <?php endif; ?>

                <div class="ig-actions">
                    <a class="btn primary" href="<?= BASE_URL ?>/artist/events.php">Caută evenimente</a>
                    <a class="btn" href="<?= BASE_URL ?>/artist/my_applications.php">Aplicările mele</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery section -->
    <div class="card" style="padding:0; border-radius:0 0 18px 18px; overflow:hidden;">

        <!-- Tab bar -->
        <div class="ig-tab-bar">
            <div class="ig-tab active" id="tabGrid" onclick="showTab('grid')">
                <span>▦</span> Galerie foto
            </div>
            <div class="ig-tab" id="tabUpload" onclick="showTab('upload')">
                <span>+</span> Adaugă poză
            </div>
        </div>

        <!-- Upload panel -->
        <div class="upload-panel" id="uploadPanel">
            <?php if ($flashError): ?>
                <div class="alert error"><?= htmlspecialchars($flashError) ?></div>
            <?php endif; ?>
            <?php if ($flashOk): ?>
                <div class="alert success"><?= htmlspecialchars($flashOk) ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                <label for="imgInput" style="display:block;">
                    <div class="drop-zone" id="dropZone">
                        <div style="font-size:48px; margin-bottom:12px;">🖼️</div>
                        <p style="font-size:15px; margin-bottom:6px;">Click pentru a selecta o poză</p>
                        <p class="muted" style="font-size:12px;">JPG, PNG, WebP, GIF — max 8MB</p>
                        <div id="dropPreview" style="margin-top:16px; display:none;">
                            <img id="dropImg" style="max-height:200px; border-radius:10px; max-width:100%;">
                        </div>
                    </div>
                </label>
                <input type="file" name="img" id="imgInput" accept="image/*" style="display:none;" required>

                <label style="margin-top:16px;">Descriere <span class="muted">(opțional)</span></label>
                <input type="text" name="desc" placeholder="ex: Concert Cluj, Sala Palatului 2024...">

                <div class="actions" style="margin-top:16px;">
                    <button class="btn primary" type="submit">📤 Încarcă poza</button>
                    <button class="btn" type="button" onclick="showTab('grid')">Anulează</button>
                </div>
            </form>
        </div>

        <!-- Grid panel -->
        <div id="gridPanel">
            <?php if (!$poze): ?>
                <div class="empty-gallery">
                    <div style="font-size:52px; margin-bottom:14px;">📷</div>
                    <p style="font-size:16px; margin-bottom:8px;">Nicio poză încă</p>
                    <p style="font-size:13px;">Adaugă poze din concerte, repetiții sau sesiuni foto!</p>
                    <button class="btn primary" onclick="showTab('upload')" style="margin-top:16px;">+ Adaugă prima poză</button>
                </div>
            <?php else: ?>
                <div class="ig-grid">
                    <?php foreach ($poze as $p): ?>
                        <div class="ig-cell">
                            <img src="<?= htmlspecialchars($p["url"]) ?>"
                                 alt="<?= htmlspecialchars($p["descriere"] ?? "") ?>">
                            <div class="cell-actions">
                                <button class="cell-view" onclick="openLb('<?= htmlspecialchars($p["url"]) ?>','<?= htmlspecialchars(addslashes($p["descriere"] ?? "")) ?>')">🔍 Vezi</button>
                                <a class="cell-del"
                                   href="?del=<?= (int)$p["id_poza"] ?>"
                                   onclick="return confirm('Ștergi această poză?');">🗑 Șterge</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Lightbox -->
<div class="lb" id="lb" onclick="closeLb()">
    <span class="lb-close">✕</span>
    <img id="lbImg" src="" alt="">
    <div class="lb-desc" id="lbDesc"></div>
</div>

<script>
function showTab(tab) {
    const isUpload = tab === 'upload';
    document.getElementById('uploadPanel').classList.toggle('open', isUpload);
    document.getElementById('gridPanel').style.display   = isUpload ? 'none' : 'block';
    document.getElementById('tabUpload').classList.toggle('active', isUpload);
    document.getElementById('tabGrid').classList.toggle('active', !isUpload);
}

// Preview imagine înainte de upload
document.getElementById('imgInput').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('dropImg').src = e.target.result;
        document.getElementById('dropPreview').style.display = 'block';
    };
    reader.readAsDataURL(file);
});

// Drag & drop pe drop zone
const dz = document.getElementById('dropZone');
const fi = document.getElementById('imgInput');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag'); });
dz.addEventListener('dragleave', () => dz.classList.remove('drag'));
dz.addEventListener('drop', e => {
    e.preventDefault();
    dz.classList.remove('drag');
    if (e.dataTransfer.files[0]) {
        fi.files = e.dataTransfer.files;
        fi.dispatchEvent(new Event('change'));
    }
});

// Lightbox
function openLb(src, desc) {
    document.getElementById('lbImg').src = src;
    document.getElementById('lbDesc').textContent = desc;
    document.getElementById('lbDesc').style.display = desc ? 'block' : 'none';
    document.getElementById('lb').classList.add('open');
    event.stopPropagation();
}
function closeLb() { document.getElementById('lb').classList.remove('open'); }
document.addEventListener('keydown', e => { if(e.key==='Escape') closeLb(); });

<?php if ($flashOk): ?>
showTab('grid');
<?php endif; ?>
</script>
</body>
</html>
