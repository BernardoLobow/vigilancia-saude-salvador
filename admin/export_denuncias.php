<?php
require_once __DIR__ . '/../api/config.php';
$db = get_db();
$rows = $db->query("SELECT * FROM denuncias ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=denuncias.csv');
$out = fopen('php://output', 'w');
fputcsv($out, ['id','created_at','local_text','description','files']);
foreach($rows as $r) {
  fputcsv($out, [$r['id'],$r['created_at'],$r['local_text'],$r['description'],$r['files']]);
}
fclose($out);
exit;
