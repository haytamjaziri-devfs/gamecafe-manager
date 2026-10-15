<?php
$pdo = new PDO('mysql:host=localhost;dbname=gamecafe;charset=utf8mb4', 'root', 'haytam2006', [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function head($t) { ?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><title><?= h($t) ?></title>
<style>
body{font-family:system-ui,sans-serif;margin:0;background:#f4f6fb;color:#222}
nav{background:#1f2a44;padding:14px 24px}nav a{color:#fff;margin-right:20px;text-decoration:none;font-weight:600}
main{max-width:960px;margin:24px auto;padding:0 16px}
.cards{display:flex;gap:16px;flex-wrap:wrap}.card{background:#fff;padding:16px 20px;border-radius:10px;flex:1;min-width:160px;box-shadow:0 1px 4px #0001}
.card b{display:block;font-size:26px;color:#2f6fd0}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;margin:12px 0 28px}
th,td{padding:10px 12px;text-align:left;border-bottom:1px solid #eee}th{background:#e8eef9}
.libre{color:#1a9a4a;font-weight:700}.occupe{color:#d33;font-weight:700}
button,input,select{padding:8px 10px;border-radius:6px;border:1px solid #ccd}button{background:#2f6fd0;color:#fff;border:0;cursor:pointer}
</style></head><body>
<nav><a href="index.php">Dashboard</a><a href="clients.php">Clients</a></nav><main>
<?php }
