<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($_POST['action'] ?? '') === 'add' && trim($_POST['nom']) !== '') {
    $pdo->prepare("INSERT INTO clients (nom, telephone, email) VALUES (?, ?, ?)")
        ->execute([trim($_POST['nom']), $_POST['telephone'] ?: null, $_POST['email'] ?: null]);
  } elseif (($_POST['action'] ?? '') === 'delete') {
    $pdo->prepare("DELETE FROM clients WHERE id = ?")->execute([$_POST['id']]);
  }
  header('Location: clients.php'); exit;
}
$clients = $pdo->query("SELECT * FROM v_top_clients")->fetchAll();
head('GameCafe - Clients');
?>
<h2>Clients</h2>
<form method="post" style="display:flex;gap:8px;flex-wrap:wrap">
  <input type="hidden" name="action" value="add">
  <input name="nom" placeholder="Nom" required><input name="telephone" placeholder="Téléphone"><input name="email" type="email" placeholder="Email">
  <button>Ajouter</button>
</form>
<table><tr><th>Nom</th><th>Sessions</th><th>Total dépensé</th><th></th></tr>
<?php foreach ($clients as $c): ?>
<tr><td><?= h($c['nom']) ?></td><td><?= h($c['nb_sessions']) ?></td><td><?= h($c['total_depense']) ?> DH</td>
<td><form method="post" onsubmit="return confirm('Supprimer ce client ?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= h($c['id']) ?>"><button>Supprimer</button></form></td></tr>
<?php endforeach; ?>
</table></main></body></html>
