<?php
session_start();

define('ADMIN_PASSWORD', 'Tovornjakmirko1');
define('EVENTS_FILE', __DIR__ . '/events.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
    } else {
        $loginError = 'Napačno geslo.';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

function loadEvents() {
    if (!file_exists(EVENTS_FILE)) return [];
    return json_decode(file_get_contents(EVENTS_FILE), true) ?? [];
}
function saveEvents($events) {
    file_put_contents(EVENTS_FILE, json_encode(array_values($events), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$message = '';
if (!empty($_SESSION['admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
        $datum = trim($_POST['datum'] ?? '');
        $opis  = trim($_POST['opis'] ?? '');
        if ($datum && $opis && preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum)) {
            $events = loadEvents();
            $events[] = ['datum' => $datum, 'opis' => htmlspecialchars($opis)];
            usort($events, fn($a, $b) => strcmp($a['datum'], $b['datum']));
            saveEvents($events);
            $message = 'Event dodan!';
        }
    }
    if (isset($_GET['delete'])) {
        $idx = (int)$_GET['delete'];
        $events = loadEvents();
        array_splice($events, $idx, 1);
        saveEvents($events);
        header('Location: admin.php?ok=1');
        exit;
    }
    if (isset($_GET['move'])) {
        [$idx, $dir] = explode(',', $_GET['move']);
        $idx = (int)$idx; $dir = (int)$dir;
        $events = loadEvents();
        $swap = $idx + $dir;
        if ($swap >= 0 && $swap < count($events)) {
            [$events[$idx], $events[$swap]] = [$events[$swap], $events[$idx]];
            saveEvents($events);
        }
        header('Location: admin.php');
        exit;
    }
}

$events = loadEvents();
?>
<!DOCTYPE html>
<html lang="sl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Oui Chef Eventi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --brown:#3D2314;
  --brown-dark:#1A0E08;
  --brown-mid:#2A1610;
  --gold:#E8A020;
  --gold-light:#F2BC50;
  --cream:#F5F0E8;
  --cream-dim:rgba(245,240,232,0.65);
  --gold-border:rgba(232,160,32,0.18);
}
body{background:var(--brown-dark);color:var(--cream);font-family:'Inter',sans-serif;min-height:100vh}

.topbar{
  background:rgba(18,8,3,0.96);
  border-bottom:1px solid var(--gold-border);
  padding:18px 48px;
  display:flex;justify-content:space-between;align-items:center;
}
.topbar-title{
  font-family:'Playfair Display',serif;
  font-size:1.1rem;font-weight:700;
  color:var(--gold);font-style:italic;
}
.topbar a{
  font-size:.72rem;font-weight:500;letter-spacing:.1em;text-transform:uppercase;
  color:var(--cream-dim);text-decoration:none;transition:color .2s;
}
.topbar a:hover{color:var(--gold)}

.container{max-width:720px;margin:3rem auto;padding:0 24px}

/* LOGIN */
.login-box{
  background:var(--brown);border:1px solid var(--gold-border);
  padding:2.5rem;max-width:380px;margin:5rem auto;
}
.login-box h1{
  font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;
  color:var(--cream);margin-bottom:1.5rem;
}
.login-error{font-size:.78rem;color:#e05050;margin-bottom:1rem}

/* FORM */
label{
  font-size:.65rem;font-weight:600;letter-spacing:.15em;text-transform:uppercase;
  color:rgba(245,240,232,.4);display:block;margin-bottom:.4rem;
}
input[type=text],input[type=password],input[type=date]{
  width:100%;background:rgba(255,255,255,.04);
  border:1px solid var(--gold-border);
  color:var(--cream);padding:.75rem 1rem;
  font-family:'Inter',sans-serif;font-size:.9rem;
  outline:none;transition:border-color .2s;
  border-radius:0;-webkit-appearance:none;
}
input:focus{border-color:rgba(232,160,32,.5)}
.form-group{margin-bottom:1rem}

.btn{
  background:var(--gold);color:var(--brown-dark);border:none;cursor:pointer;
  font-family:'Inter',sans-serif;font-size:.78rem;font-weight:700;
  letter-spacing:.1em;text-transform:uppercase;
  padding:.85rem 2rem;transition:background .2s;
}
.btn:hover{background:var(--gold-light)}

.btn-sm{
  background:transparent;border:1px solid var(--gold-border);color:var(--cream-dim);
  font-size:.6rem;font-weight:500;letter-spacing:.1em;text-transform:uppercase;
  padding:.3rem .65rem;cursor:pointer;text-decoration:none;
  display:inline-block;transition:border-color .2s,color .2s;
}
.btn-sm:hover{border-color:var(--gold);color:var(--gold)}
.btn-del{border-color:rgba(200,80,80,.3);color:rgba(200,80,80,.7)}
.btn-del:hover{border-color:#e05050;color:#e05050;background:rgba(200,80,80,.08)}

/* ADD SECTION */
.add-section{
  background:var(--brown);border:1px solid var(--gold-border);
  padding:2rem;margin-bottom:2.5rem;
}
.add-section h2{
  font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;
  font-style:italic;color:var(--gold);margin-bottom:1.5rem;
}
.add-row{display:grid;grid-template-columns:160px 1fr auto;gap:.75rem;align-items:end}
@media(max-width:540px){.add-row{grid-template-columns:1fr}}

/* EVENTS LIST */
.events-section h2{
  font-size:.65rem;font-weight:600;letter-spacing:.2em;text-transform:uppercase;
  color:rgba(245,240,232,.35);margin-bottom:1rem;
}
.event-row{
  display:flex;align-items:center;gap:1rem;
  background:var(--brown);border:1px solid var(--gold-border);
  padding:1rem 1.2rem;margin-bottom:.5rem;
}
.event-datum{
  font-size:.72rem;font-weight:600;letter-spacing:.08em;
  background:rgba(232,160,32,.12);color:var(--gold);
  padding:.3rem .65rem;white-space:nowrap;min-width:70px;text-align:center;
  border:1px solid var(--gold-border);
}
.event-opis{flex:1;font-size:.9rem;color:var(--cream)}
.event-actions{display:flex;gap:.4rem;flex-shrink:0}
.move-btns{display:flex;flex-direction:column;gap:2px}
.move-btns a{font-size:.55rem;padding:.2rem .4rem;line-height:1}

.msg-ok{font-size:.75rem;color:#6fcf6f;margin-bottom:1.2rem;letter-spacing:.05em}
.empty{
  font-size:.72rem;color:var(--cream-dim);padding:1.5rem;text-align:center;
  border:1px dashed var(--gold-border);
}
.hint{font-size:.65rem;color:rgba(245,240,232,.3);margin-top:.5rem;letter-spacing:.05em}
</style>
</head>
<body>

<div class="topbar">
  <span class="topbar-title">Oui Chef — Admin</span>
  <?php if (!empty($_SESSION['admin'])): ?>
    <a href="?logout=1">Odjava</a>
  <?php else: ?>
    <a href="index.html">← Nazaj na stran</a>
  <?php endif; ?>
</div>

<?php if (empty($_SESSION['admin'])): ?>
<div class="container">
  <div class="login-box">
    <h1>Prijava</h1>
    <?php if (!empty($loginError)): ?>
      <div class="login-error"><?= $loginError ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Geslo</label>
        <input type="password" name="password" autofocus required>
      </div>
      <button class="btn" type="submit">Prijava →</button>
    </form>
  </div>
</div>

<?php else: ?>
<div class="container">

  <?php if ($message): ?><div class="msg-ok">✓ <?= $message ?></div><?php endif; ?>
  <?php if (isset($_GET['ok'])): ?><div class="msg-ok">✓ Event izbrisan.</div><?php endif; ?>

  <div class="add-section">
    <h2>Dodaj event</h2>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="add-row">
        <div class="form-group" style="margin:0">
          <label>Datum</label>
          <input type="date" name="datum" required>
        </div>
        <div class="form-group" style="margin:0">
          <label>Opis / lokacija</label>
          <input type="text" name="opis" placeholder="Street Food Festival — Ljubljana" required>
        </div>
        <button class="btn" type="submit" style="white-space:nowrap">Dodaj +</button>
      </div>
      <p class="hint">Dogodki se samodejno sortirajo po datumu. Pretekli se skrijejo na strani.</p>
    </form>
  </div>

  <div class="events-section">
    <h2>Trenutni eventi (<?= count($events) ?>)</h2>
    <?php if (empty($events)): ?>
      <div class="empty">Ni eventov. Dodaj prvega zgoraj.</div>
    <?php else: ?>
      <?php foreach ($events as $i => $ev): ?>
        <div class="event-row">
          <div class="move-btns">
            <?php if ($i > 0): ?><a class="btn-sm" href="?move=<?= $i ?>,<?= -1 ?>">▲</a><?php endif; ?>
            <?php if ($i < count($events) - 1): ?><a class="btn-sm" href="?move=<?= $i ?>,<?= 1 ?>">▼</a><?php endif; ?>
          </div>
          <span class="event-datum"><?= date('d.m.Y', strtotime($ev['datum'])) ?></span>
          <span class="event-opis"><?= htmlspecialchars($ev['opis']) ?></span>
          <div class="event-actions">
            <a class="btn-sm btn-del" href="?delete=<?= $i ?>" onclick="return confirm('Izbriši ta event?')">Briši</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>
<?php endif; ?>

</body>
</html>