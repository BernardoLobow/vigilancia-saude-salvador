<?php

function save_uploaded_images($files_array, $prefix='img') {
    $saved = [];
    $allowed = ['image/jpeg','image/jpg','image/png','image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5 Megabytes em bytes

    for ($i = 0; $i < count($files_array['name']); $i++) {
        // Pula se houver erro ou se o arquivo for maior que o permitido
        if ($files_array['error'][$i] !== UPLOAD_ERR_OK) continue;
        if ($files_array['size'][$i] > $maxSize) continue; // Melhoria de segurança

        $tmp = $files_array['tmp_name'][$i];
        // ... restante do seu código de upload
    }
    return $saved;
}
// api/config.php
header('Content-Type: application/json; charset=utf-8');

// caminho do sqlite
define('DB_FILE', __DIR__ . '/../data/database.sqlite');

// pasta para uploads (relativo a project root)
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// url pública base (ajuste se necessário)
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);

// cria pastas se não existirem
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(dirname(DB_FILE))) {
    mkdir(dirname(DB_FILE), 0755, true);
}

// função utilitária para conectar SQLite
function get_db() {
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // cria tabelas se não existirem
    $db->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        profile TEXT,
        prof_type TEXT,
        notif_type TEXT,
        date_start TEXT,
        local_text TEXT,
        description TEXT,
        sought_care TEXT,
        unit TEXT,
        contact TEXT,
        consent INTEGER,
        files TEXT
    )");
    $db->exec("CREATE TABLE IF NOT EXISTS denuncias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        local_text TEXT,
        description TEXT,
        files TEXT
    )");
    return $db;
}

// função utilitária para salvar upload (retorna array de urls)
function save_uploaded_images($files_array, $prefix='img') {
    $saved = [];
    $allowed = ['image/jpeg','image/jpg','image/png','image/webp'];
    for ($i = 0; $i < count($files_array['name']); $i++) {
        if ($files_array['error'][$i] !== UPLOAD_ERR_OK) continue;
        $tmp = $files_array['tmp_name'][$i];
        $type = mime_content_type($tmp);
        if (!in_array($type, $allowed)) continue;
        $ext = pathinfo($files_array['name'][$i], PATHINFO_EXTENSION);
        $name = $prefix . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = UPLOAD_DIR . $name;
        if (move_uploaded_file($tmp, $dest)) {
            $saved[] = $name; // salva somente o nome; montamos URL no frontend se quiser
        }
    }
    return $saved;
}
