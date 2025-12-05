<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

$db = getDB();
$sql = "SELECT e.id, e.titulo, e.data_evento, e.horario_evento, e.status_evento, e.nome_cliente, e.local FROM eventos e ORDER BY e.data_evento";
$eventos = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($eventos);