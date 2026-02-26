<?php
require_once __DIR__ . '/config.php';

// habilita erros só em dev (comente em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    $local_text = trim($_POST['local'] ?? '');
    $description = trim($_POST['descricao'] ?? '');

    if ($local_text === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Local é obrigatório']);
        exit;
    }

    // Função auxiliar para processar arrays de arquivos e single files
    function saveFilesGeneric($filesArray, $prefix='denuncia') {
        $saved = [];
        $allowed = ['image/jpeg','image/jpg','image/png','image/webp'];
        // se for array estilo denunciaFiles[] (name => array)
        if (isset($filesArray['name']) && is_array($filesArray['name'])) {
            for ($i = 0; $i < count($filesArray['name']); $i++) {
                if ($filesArray['error'][$i] !== UPLOAD_ERR_OK) continue;
                $tmp = $filesArray['tmp_name'][$i];
                if (!is_uploaded_file($tmp)) continue;
                $type = @mime_content_type($tmp);
                if (!in_array($type, $allowed)) continue;
                $ext = pathinfo($filesArray['name'][$i], PATHINFO_EXTENSION) ?: 'jpg';
                $name = $prefix . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = UPLOAD_DIR . $name;
                if (move_uploaded_file($tmp, $dest)) $saved[] = $name;
            }
        } else {
            // single file input
            if (isset($filesArray['tmp_name']) && $filesArray['error'] === UPLOAD_ERR_OK) {
                $tmp = $filesArray['tmp_name'];
                if (is_uploaded_file($tmp)) {
                    $type = @mime_content_type($tmp);
                    if (in_array($type, $allowed)) {
                        $ext = pathinfo($filesArray['name'], PATHINFO_EXTENSION) ?: 'jpg';
                        $name = $prefix . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                        $dest = UPLOAD_DIR . $name;
                        if (move_uploaded_file($tmp, $dest)) $saved[] = $name;
                    }
                }
            }
        }
        return $saved;
    }

    $allSaved = [];

    // caso o front envie denunciaFiles[] como array:
    if (isset($_FILES['denunciaFiles'])) {
        $allSaved = array_merge($allSaved, saveFilesGeneric($_FILES['denunciaFiles'], 'denuncia'));
    }

    // também verifica qualquer outro $_FILES (file_0, file_1, etc)
    foreach ($_FILES as $key => $fileEntry) {
        if ($key === 'denunciaFiles') continue;
        $saved = saveFilesGeneric($fileEntry, 'denuncia');
        if (!empty($saved)) $allSaved = array_merge($allSaved, $saved);
    }

    // grava no DB
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO denuncias (local_text, description, files) VALUES (:local_text, :description, :files)");
    $stmt->execute([
        ':local_text' => $local_text,
        ':description' => $description,
        ':files' => json_encode($allSaved)
    ]);

    echo json_encode(['success' => true, 'id' => $db->lastInsertId(), 'files' => $allSaved]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno', 'detail' => $e->getMessage()]);
}
