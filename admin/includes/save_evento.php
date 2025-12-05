<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

$db = getDB();
$id = $_POST['id'] ?? null;

if ($id) {
    $sql = "UPDATE eventos SET titulo=?, data_evento=?, horario_evento=?, status_evento=?, nome_cliente=?, local=? WHERE id=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$_POST['titulo'], $_POST['data_evento'], $_POST['horario_evento'], $_POST['status_evento'], $_POST['nome_cliente'], $_POST['local'], $id]);
} else {
    $sql = "INSERT INTO eventos (titulo, data_evento, horario_evento, status_evento, nome_cliente, local, criado_por) VALUES (?,?,?,?,?,?,1)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$_POST['titulo'], $_POST['data_evento'], $_POST['horario_evento'], $_POST['status_evento'], $_POST['nome_cliente'], $_POST['local']]);
}

echo json_encode(['success' => true]);