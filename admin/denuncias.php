<?php
require_once __DIR__ . '/../api/config.php';
$db = get_db();
$rows = $db->query("SELECT id, created_at, local_text, description, files FROM denuncias ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Admin - Denúncias</title></head>
<body style="font-family:Arial;padding:20px;">
<h2>Denúncias</h2>
<table border="1" cellpadding="6" style="border-collapse:collapse;">
<tr><th>ID</th><th>Data</th><th>Local</th><th>Descrição</th><th>Imagens</th></tr>
<?php foreach($rows as $r): ?>
  <tr>
    <td><?=htmlspecialchars($r['id'])?></td>
    <td><?=htmlspecialchars($r['created_at'])?></td>
    <td><?=htmlspecialchars($r['local_text'])?></td>
    <td><?=nl2br(htmlspecialchars($r['description']))?></td>
    <td>
      <?php
        $files = json_decode($r['files'], true) ?: [];
        foreach($files as $f) {
          $url = '/vigilancia/uploads/' . rawurlencode($f);
          echo "<a href=\"{$url}\" target=\"_blank\"><img src=\"{$url}\" style=\"width:100px;margin:4px;border-radius:6px\"></a>";
        }
      ?>
    </td>
  </tr>
<?php endforeach; ?>
</table>
</body></html>
