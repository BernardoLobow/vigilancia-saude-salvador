<?php
require_once __DIR__ . '/config.php';

// Permitir apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    // lê campos (ajuste nomes conforme seu form)
    // No notifications.php, altere para:
    $nome = $_POST['nome'] ?? '';
    $idade = $_POST['idade'] ?? '';
    $viagem = $_POST['viagem'] ?? '';
    $profile = $_POST['profile'] ?? '';
    $prof_type = $_POST['prof_type'] ?? '';
    $notif_type = $_POST['type'] ?? '';
    $date_start = $_POST['date'] ?? '';
    $local_text = $_POST['local'] ?? '';
    $description = $_POST['desc'] ?? '';
    $sought_care = $_POST['sought_care'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $consent = (isset($_POST['consent']) && $_POST['consent'] === 'yes') ? 1 : 0;

    // validações básicas
    if (empty($profile) || empty($notif_type) || empty($date_start) || empty($local_text)) {
        http_response_code(400);
        echo json_encode(['error' => 'Campos obrigatórios ausentes']);
        exit;
    }

    // salva imagens (se houver)
    $saved_files = [];
    if (!empty($_FILES['nfFiles'])) {
        $saved_files = save_uploaded_images($_FILES['nfFiles'], 'notif');
    }

    // salva no DB
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO notifications
        (profile, prof_type, notif_type, date_start, local_text, description, sought_care, unit, contact, consent, files)
        VALUES (:profile, :prof_type, :notif_type, :date_start, :local_text, :description, :sought_care, :unit, :contact, :consent, :files)");
    $stmt->execute([
        ':profile' => $profile,
        ':prof_type' => $prof_type,
        ':notif_type' => $notif_type,
        ':date_start' => $date_start,
        ':local_text' => $local_text,
        ':description' => $description,
        ':sought_care' => $sought_care,
        ':unit' => $unit,
        ':contact' => $contact,
        ':consent' => $consent,
        ':files' => json_encode($saved_files)
    ]);

    echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno', 'detail' => $e->getMessage()]);
}
