<?php
require_once __DIR__ . "/../config/init.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}

$msg = "";
$err = "";

// Ștergere cont
if (isset($_GET["del_artist"]) && is_numeric($_GET["del_artist"])) {
    $id = (int)$_GET["del_artist"];
    $db->updateDB("DELETE FROM artist_poze WHERE id_artist = ?", [$id]);
    $db->updateDB("DELETE FROM aplicare WHERE id_artist = ?", [$id]);
    $db->updateDB("DELETE FROM artist WHERE id_artist = ?", [$id]);
    $msg = "Contul de artist a fost șters.";
}

if (isset($_GET["del_spectator"]) && is_numeric($_GET["del_spectator"])) {
    $id = (int)$_GET["del_spectator"];
    $db->updateDB("DELETE FROM tbl_cart WHERE id_spectator = ?", [$id]);
    $db->updateDB("DELETE FROM bilet WHERE id_spectator = ?", [$id]);
    $db->updateDB("DELETE FROM spectator WHERE id_spectator = ?", [$id]);
    $msg = "Contul de spectator a fost șters.";
}

// Fetch users
$artisti    = $db->getDBResult("SELECT id_artist, nume, email, telefon, gen_muzical FROM artist ORDER BY id_artist") ?: [];
$spectatori = $db->getDBResult("SELECT id_spectator, nume, email, telefon FROM spectator ORDER BY id_spectator") ?: [];

$tab = $_GET["tab"] ?? "artisti";
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Gestionare conturi · MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <style>
        .tab-bar { display:flex; gap:4px; margin-bottom:20px; }
        .tab-btn { padding:9px 20px; border-radius:10px; cursor:pointer; font-size:14px; border:none; font-weight:600; transition:.2s; }
        .tab-btn.active { background:linear-gradient(135deg,#a18cd1,#fbc2eb); color:#1f1f1f; }
        .tab-btn:not(.active) { background:rgba(255,255,255,.1); color:#cfc7ff; }
        .tab-btn:not(.active):hover { background:rgba(255,255,255,.18); }
        .user-count { font-size:12px; font-weight:400; opacity:.75; margin-left:6px; }
        .del-btn { background:linear-gradient(135deg,#ff6b6b,#ffb3b3); color:#fff; border:none; padding:5px 12px; border-radius:8px; font-size:12px; cursor:pointer; text-decoration:none; white-space:nowrap; }
        .del-btn:hover { opacity:.85; }
        td { vertical-align:middle; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand">
            <h1>👥 Gestionare conturi</h1>
            <p class="muted">Vizualizează și șterge conturi de artiști și spectatori</p>
        </div>
        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/admin/admin_home.php">← Admin</a>
            <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert success" style="margin-bottom:20px;"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="tab-bar">
        <button class="tab-btn <?= $tab === 'artisti' ? 'active' : '' ?>"
                onclick="switchTab('artisti')">
            🎸 Artiști <span class="user-count">(<?= count($artisti) ?>)</span>
        </button>
        <button class="tab-btn <?= $tab === 'spectatori' ? 'active' : '' ?>"
                onclick="switchTab('spectatori')">
            🎟️ Spectatori <span class="user-count">(<?= count($spectatori) ?>)</span>
        </button>
    </div>

    <!-- Artiști -->
    <div id="panel-artisti" style="display:<?= $tab === 'artisti' ? 'block' : 'none' ?>">
        <div class="card" style="padding:0; overflow:hidden;">
            <?php if (!$artisti): ?>
                <p style="padding:30px; text-align:center; color:#cfc7ff;">Niciun artist înregistrat.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nume</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Gen muzical</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $index = 0;
                        foreach ($artisti as $artist): 
                        $index++;
                        ?>
                            <tr>
                            <td><?php echo $index; ?></td> <!-- Număr secvențial -->
        <td><?php echo $artist['nume']; ?></td>
        <td><?php echo $artist['email']; ?></td>
        <td><?php echo $artist['telefon']; ?></td>
        <td><?php echo $artist['gen_muzical']; ?></td>
        <td>
    <a class="del-btn"
       href="?tab=artisti&del_artist=<?= (int)$artist["id_artist"] ?>"
       onclick="return confirm('Ștergi contul artistului <?= htmlspecialchars(addslashes($artist["nume"])) ?>? Acțiunea este ireversibilă!');">
        🗑 Șterge
    </a>
</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Spectatori -->
    <div id="panel-spectatori" style="display:<?= $tab === 'spectatori' ? 'block' : 'none' ?>">
        <div class="card" style="padding:0; overflow:hidden;">
            <?php if (!$spectatori): ?>
                <p style="padding:30px; text-align:center; color:#cfc7ff;">Niciun spectator înregistrat.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nume</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($spectatori as $s): ?>
                            <tr>
                                <td><?= (int)$s["id_spectator"] ?></td>
                                <td><strong><?= htmlspecialchars($s["nume"]) ?></strong></td>
                                <td><?= htmlspecialchars($s["email"]) ?></td>
                                <td><?= htmlspecialchars($s["telefon"]) ?></td>
                                <td>
                                    <a class="del-btn"
                                       href="?tab=spectatori&del_spectator=<?= (int)$s["id_spectator"] ?>"
                                       onclick="return confirm('Ștergi contul spectatorului <?= htmlspecialchars(addslashes($s["nume"])) ?>? Acțiunea este ireversibilă!');">
                                        🗑 Șterge
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>
<script>
function switchTab(tab) {
    ['artisti','spectatori'].forEach(t => {
        document.getElementById('panel-' + t).style.display = t === tab ? 'block' : 'none';
        document.querySelectorAll('.tab-btn').forEach((btn, i) => {
            btn.classList.toggle('active', (i === 0 && tab === 'artisti') || (i === 1 && tab === 'spectatori'));
        });
    });
}
</script>
</body>
</html>
