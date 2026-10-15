<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_POST['action'] === 'start') {
    $pdo->prepare("INSERT INTO sessions (client_id, poste_id) VALUES (?, ?)")
        ->execute([$_POST['client_id'], $_POST['poste_id']]);
  } elseif ($_POST['action'] === 'stop') {
    $pdo->prepare("UPDATE sessions SET fin = NOW() WHERE id = ? AND fin IS NULL")
        ->execute([$_POST['session_id']]);
  }
  header('Location: index.php'); exit;
}

$ca = $pdo->query("SELECT COALESCE(SUM(chiffre_affaires),0) FROM v_ca_journalier WHERE jour = CURDATE()")->fetchColumn();
$libres = $pdo->query("SELECT COUNT(*) FROM postes WHERE statut='libre'")->fetchColumn();
$nbClients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$postes = $pdo->query("SELECT p.*, s.id AS session_id, c.nom AS client FROM postes p
  LEFT JOIN sessions s ON s.poste_id = p.id AND s.fin IS NULL
  LEFT JOIN clients c ON c.id = s.client_id ORDER BY p.nom")->fetchAll();
$clients = $pdo->query("SELECT id, nom FROM clients ORDER BY nom")->fetchAll();
$recent = $pdo->query("SELECT c.nom, p.nom AS poste, s.debut, s.fin, s.montant FROM sessions s
  JOIN clients c ON c.id = s.client_id JOIN postes p ON p.id = s.poste_id
  ORDER BY s.debut DESC LIMIT 8")->fetchAll();
head('GameCafe - Dashboard');
?>
<h2>Dashboard</h2>
<div class="cards">
  <div class="card">CA du jour<b><?= h(number_format($ca, 2)) ?> DH</b></div>
  <div class="card">Postes libres<b><?= h($libres) ?> / <?= count($postes) ?></b></div>
  <div class="card">Clients<b><?= h($nbClients) ?></b></div>
</div>

<h3>Postes</h3>
<table><tr><th>Poste</th><th>Type</th><th>Tarif/h</th><th>Statut</th><th>Action</th></tr>
<?php foreach ($postes as $p): ?>
<tr><td><?= h($p['nom']) ?></td><td><?= h($p['type']) ?></td><td><?= h($p['tarif_heure']) ?> DH</td>
<td class="<?= h($p['statut']) ?>"><?= h($p['statut']) ?><?= $p['client'] ? ' ('.h($p['client']).')' : '' ?></td>
<td>
<?php if ($p['statut'] === 'libre'): ?>
  <form method="post" style="display:flex;gap:6px"><input type="hidden" name="action" value="start">
  <input type="hidden" name="poste_id" value="<?= h($p['id']) ?>">
  <select name="client_id"><?php foreach ($clients as $c): ?><option value="<?= h($c['id']) ?>"><?= h($c['nom']) ?></option><?php endforeach; ?></select>
  <button>Démarrer</button></form>
<?php elseif ($p['session_id']): ?>
  <form method="post"><input type="hidden" name="action" value="stop"><input type="hidden" name="session_id" value="<?= h($p['session_id']) ?>"><button>Terminer</button></form>
<?php endif; ?>
</td></tr>
<?php endforeach; ?>
</table>

<h3>Dernières sessions</h3>
<table><tr><th>Client</th><th>Poste</th><th>Début</th><th>Fin</th><th>Montant</th></tr>
<?php foreach ($recent as $r): ?>
<tr><td><?= h($r['nom']) ?></td><td><?= h($r['poste']) ?></td><td><?= h($r['debut']) ?></td>
<td><?= h($r['fin'] ?? 'en cours') ?></td><td><?= $r['montant'] !== null ? h($r['montant']).' DH' : '-' ?></td></tr>
<?php endforeach; ?>
</table></main></body></html>
