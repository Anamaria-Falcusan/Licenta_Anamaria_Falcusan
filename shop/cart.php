<?php
require_once __DIR__ . "/../config/init.php";

$loggedIn     = isset($_SESSION["id_spectator"]);
$id_spectator = $loggedIn ? (int)$_SESSION["id_spectator"] : 0;

$items = [];
$total = 0;
$error = $_GET["error"] ?? "";

if ($loggedIn) {
    $items = $db->getDBResult(
        "SELECT c.id AS cart_id, c.quantity,
                e.id_eveniment, e.nume, e.pret_bilet, e.locatie, e.data_eveniment, e.bilete_disponibile
         FROM tbl_cart c
         JOIN eveniment e ON e.id_eveniment = c.id_eveniment
         WHERE c.id_spectator = ?",
        [$id_spectator]
    );

    foreach ($items as $it) {
        $total += ((float)$it["pret_bilet"] * (int)$it["quantity"]);
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Coș bilete - MusicConnect</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand">
            <h1>🛒 Coșul meu</h1>
            <p class="muted">Revizuiește biletele înainte de plată</p>
        </div>
        <div class="nav">
            <a class="btn" href="<?= BASE_URL ?>/shop/index.php">Continuă cumpărăturile</a>
            <?php if ($loggedIn): ?>
                <a class="btn danger" href="<?= BASE_URL ?>/auth/logout.php">Logout</a>
            <?php else: ?>
                <a class="btn" href="<?= BASE_URL ?>/auth/login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$loggedIn): ?>
        <div class="card">
            <p>Trebuie să fii logat ca să vezi coșul.</p>
            <div class="actions">
                <a class="btn primary" href="<?= BASE_URL ?>/auth/login.php">Login</a>
                <a class="btn" href="<?= BASE_URL ?>/auth/register.php">Register</a>
            </div>
        </div>

        <?php if ($error === "notenough"): ?>
    <div class="alert error" style="margin-bottom:15px;">
        Nu există suficiente bilete disponibile pentru cantitatea selectată.
    </div>
<?php endif; ?>

    <?php elseif (!$items): ?>
        <div class="card" style="text-align:center; padding:40px;">
            <p style="font-size:48px; margin-bottom:15px;">🛒</p>
            <p style="font-size:18px; margin-bottom:20px;">Coșul tău este gol.</p>
            <a class="btn primary" href="<?= BASE_URL ?>/shop/index.php">Vezi evenimentele</a>
        </div>

    <?php else: ?>
        <div class="card">
            <table>
                <tr>
                    <th>Eveniment</th>
                    <th>Data / Locație</th>
                    <th>Preț/bilet</th>
                    <th>Cantitate</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>

                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($it["nume"]) ?></strong></td>
                        <td class="muted" style="font-size:13px;">
                            <?= htmlspecialchars($it["data_eveniment"]) ?><br>
                            <?= htmlspecialchars($it["locatie"]) ?>
                        </td>
                        <td><?= number_format((float)$it["pret_bilet"], 2) ?> lei</td>
                        <td>
                            <form action="<?= BASE_URL ?>/shop/updateCart.php" method="post" style="display:flex; gap:6px; align-items:center;">
                                <input type="hidden" name="cart_id" value="<?= (int)$it["cart_id"] ?>">
                                <input type="number" name="quantity" min="1"
       max="<?= min(10, (int)$it["quantity"] + (int)$it["bilete_disponibile"]) ?>"
       value="<?= (int)$it["quantity"] ?>"
       style="width:55px; padding:4px 6px;">
                                <button class="btn" type="submit" style="padding:4px 10px;">↻</button>
                            </form>
                        </td>
                        <td><strong><?= number_format((float)$it["pret_bilet"] * (int)$it["quantity"], 2) ?> lei</strong></td>
                        <td>
                            <a class="btn danger"
                               href="<?= BASE_URL ?>/shop/removeFromCart.php?cart_id=<?= (int)$it["cart_id"] ?>"
                               onclick="return confirm('Ștergi acest bilet din coș?');">✕</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <hr style="margin: 25px 0; opacity:0.2;">

            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px;">

                <div>
                    <p style="font-size: 22px;">
                        Total: <strong style="color:#fbc2eb;"><?= number_format($total, 2) ?> lei</strong>
                    </p>
                    <p class="muted" style="font-size:13px;">Plata se face securizat prin Stripe</p>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                 
                    <form method="post" action="<?= BASE_URL ?>/shop/stripe_cart_checkout.php">
                        <button class="btn primary" type="submit" style="font-size:16px; padding:12px 28px;">
                            💳 Plătește cu cardul
                        </button>
                    </form>

                    <a class="btn danger"
                       href="<?= BASE_URL ?>/shop/emptyCart.php"
                       onclick="return confirm('Sigur vrei să golești coșul?');">
                        🗑 Golește coșul
                    </a>
                </div>

            </div>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
