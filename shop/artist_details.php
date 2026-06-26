<?php
require_once __DIR__ . "/../config/init.php";

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) { header("Location: " . BASE_URL . "/shop/artists.php"); exit; }

$rows = $db->getDBResult("SELECT * FROM artist WHERE id_artist = ?", [$id]);
if (!$rows) die("Artist inexistent.");
$artist = $rows[0];

$poze = $db->getDBResult(
    "SELECT id_poza, url, descriere FROM artist_poze WHERE id_artist = ? ORDER BY id_poza DESC",
    [$id]
);

$events = $db->getDBResult(
    "SELECT e.nume, e.locatie, e.data_eveniment
     FROM aplicare a JOIN eveniment e ON e.id_eveniment = a.id_eveniment
     WHERE a.id_artist = ? AND a.status = 'acceptata'
     ORDER BY e.data_eveniment DESC LIMIT 6",
    [$id]
);

$isSpectator  = isset($_SESSION["id_spectator"]);
$isArtist     = isset($_SESSION["id_artist"]);
$isAdmin      = isset($_SESSION["admin_id"]);
$isOwn        = $isArtist && (int)$_SESSION["id_artist"] === $id;
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($artist["nume"]) ?> · MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <style>
        .ig-header { display:flex; gap:50px; align-items:flex-start; padding:30px; background:linear-gradient(145deg,#1f2f63,#253a75); border-radius:18px 18px 0 0; margin-bottom:0; }
        .ig-avatar-col { flex-shrink:0; }
        .ig-avatar { width:150px; height:150px; border-radius:50%; object-fit:cover; border:3px solid transparent; background:linear-gradient(#253a75,#1f2f63) padding-box, linear-gradient(135deg,#f9a8d4,#a78bfa) border-box; }
        .ig-avatar-empty { width:150px; height:150px; border-radius:50%; background:linear-gradient(135deg,#2b3c78,#1f2f63); border:3px solid #fbc2eb; display:flex; align-items:center; justify-content:center; font-size:56px; }
        .ig-info { flex:1; padding-top:8px; }
        .ig-username { font-size:22px; font-weight:300; color:#ffeefc; margin-bottom:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .ig-stats { display:flex; gap:36px; margin-bottom:18px; }
        .ig-stat strong { display:block; font-size:17px; font-weight:700; color:#ffeefc; }
        .ig-stat span { font-size:13px; color:#cfc7ff; }
        .ig-genre { display:inline-block; padding:3px 12px; border-radius:20px; background:linear-gradient(135deg,#fbc2eb,#a6c1ee); color:#1f1f1f; font-weight:600; font-size:12px; margin-bottom:10px; }
        .ig-bio { font-size:14px; color:#e0daff; line-height:1.7; max-width:480px; white-space:pre-line; }

        .ig-tab-bar { display:flex; justify-content:center; background:linear-gradient(145deg,#1f2f63,#253a75); border-top:1px solid rgba(255,255,255,.08); }
        .ig-tab { padding:13px 28px; font-size:12px; letter-spacing:1.5px; text-transform:uppercase; color:#cfc7ff; border-top:2px solid transparent; display:flex; align-items:center; gap:6px; }
        .ig-tab.active { color:#ffeefc; border-top-color:#ffeefc; }

        .ig-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:3px; background:linear-gradient(145deg,#1f2f63,#253a75); border-radius:0 0 18px 18px; overflow:hidden; }
        .ig-cell { position:relative; aspect-ratio:1; overflow:hidden; background:#1a2550; cursor:pointer; }
        .ig-cell img { width:100%; height:100%; object-fit:cover; transition:transform .3s,filter .3s; display:block; }
        .ig-cell:hover img { transform:scale(1.06); filter:brightness(.65); }
        .ig-cell .cell-overlay { position:absolute; inset:0; display:flex; align-items:flex-end; padding:8px; opacity:0; transition:opacity .3s; background:linear-gradient(transparent,rgba(0,0,0,.6)); }
        .ig-cell:hover .cell-overlay { opacity:1; }
        .cell-desc { font-size:12px; color:#fff; }

        .empty-gallery { text-align:center; padding:50px 20px; color:#cfc7ff; background:linear-gradient(145deg,#1f2f63,#253a75); border-radius:0 0 18px 18px; }

        .events-card { margin-top:20px; }
        .ev-list { list-style:none; }
        .ev-list li { padding:11px 0; border-bottom:1px solid rgba(255,255,255,.08); font-size:14px; display:flex; align-items:center; gap:10px; }
        .ev-list li:last-child { border:none; }

        .lb { display:none; position:fixed; inset:0; background:rgba(0,0,0,.93); z-index:999; align-items:center; justify-content:center; }
        .lb.open { display:flex; }
        .lb img { max-width:92vw; max-height:88vh; border-radius:6px; }
        .lb-close { position:fixed; top:18px; right:24px; font-size:32px; color:#fff; cursor:pointer; }
        .lb-desc { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); color:#fff; font-size:14px; background:rgba(0,0,0,.5); padding:6px 16px; border-radius:20px; white-space:nowrap; }

        @media(max-width:680px){
            .ig-header { flex-direction:column; align-items:center; text-align:center; gap:20px; }
            .ig-stats { justify-content:center; }
            .ig-grid { grid-template-columns:repeat(2,1fr); }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand"><h1>MusicConnect</h1></div>
        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/artists.php">← Artiști</a>
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Evenimente</a>
            <?php if ($isOwn): ?>
                <a class="btn" href="<?= BASE_URL ?>/artist/dashboard.php">Dashboard</a>
            <?php endif; ?>
            <?php if ($isSpectator || $isArtist || $isAdmin): ?>
                <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
            <?php else: ?>
                <a class="btn" href="<?= BASE_URL ?>/auth/login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>

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
                <?php if ($isOwn): ?>
                    <a class="btn" href="<?= BASE_URL ?>/artist/profile_edit.php" style="font-size:12px; padding:5px 14px;">✏️ Editează</a>
                <?php endif; ?>
            </div>

            <div class="ig-stats">
                <div class="ig-stat"><strong><?= count($poze) ?></strong><span>poze</span></div>
                <div class="ig-stat"><strong><?= count($events) ?></strong><span>evenimente</span></div>
            </div>

            <?php if (!empty($artist["gen_muzical"])): ?>
                <div style="margin-bottom:10px;"><span class="ig-genre">🎵 <?= htmlspecialchars($artist["gen_muzical"]) ?></span></div>
            <?php endif; ?>

            <?php if (!empty($artist["descriere"])): ?>
                <p class="ig-bio"><?= htmlspecialchars($artist["descriere"]) ?></p>
            <?php endif; ?>

            <?php if (!empty($artist["website"])): ?>
                <p style="margin-top:8px; font-size:13px;">🔗 <a href="<?= htmlspecialchars($artist["website"]) ?>" target="_blank"><?= htmlspecialchars($artist["website"]) ?></a></p>
            <?php endif; ?>

            <?php if (!empty($artist["data_debut"])): ?>
                <p class="muted" style="margin-top:6px; font-size:13px;">🎤 Activ din <?= htmlspecialchars($artist["data_debut"]) ?></p>
            <?php endif; ?>
        </div>
    </div>


    <div class="ig-tab-bar">
        <div class="ig-tab active">▦ &nbsp;Galerie foto</div>
    </div>

 
    <?php if (!$poze): ?>
        <div class="empty-gallery">
            <div style="font-size:48px; margin-bottom:12px;">📷</div>
            <p>Nicio poză adăugată încă.</p>
        </div>
    <?php else: ?>
        <div class="ig-grid">
            <?php foreach ($poze as $p): ?>
                <div class="ig-cell" onclick="openLb('<?= htmlspecialchars($p["url"]) ?>','<?= htmlspecialchars(addslashes($p["descriere"] ?? "")) ?>')">
                    <img src="<?= htmlspecialchars($p["url"]) ?>" alt="<?= htmlspecialchars($p["descriere"] ?? "") ?>">
                    <?php if (!empty($p["descriere"])): ?>
                        <div class="cell-overlay"><span class="cell-desc"><?= htmlspecialchars($p["descriere"]) ?></span></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

 
    <?php if ($events): ?>
        <div class="card events-card">
            <h2 style="margin-bottom:16px;">🎪 Evenimente la care a participat</h2>
            <ul class="ev-list">
                <?php foreach ($events as $ev): ?>
                    <li>
                        <span style="font-size:18px;">🎵</span>
                        <div>
                            <strong><?= htmlspecialchars($ev["nume"]) ?></strong>
                            <span class="muted"> · <?= htmlspecialchars($ev["locatie"]) ?> · <?= htmlspecialchars($ev["data_eveniment"]) ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

</div>


<div class="lb" id="lb" onclick="closeLb()">
    <span class="lb-close">✕</span>
    <img id="lbImg" src="" alt="">
    <div class="lb-desc" id="lbDesc" style="display:none;"></div>
</div>

<script>
function openLb(src, desc) {
    event.stopPropagation();
    document.getElementById('lbImg').src = src;
    const d = document.getElementById('lbDesc');
    d.textContent = desc;
    d.style.display = desc ? 'block' : 'none';
    document.getElementById('lb').classList.add('open');
}
function closeLb() { document.getElementById('lb').classList.remove('open'); }
document.addEventListener('keydown', e => { if(e.key==='Escape') closeLb(); });
</script>
</body>
</html>
