<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

$db = getDB();
$id = $_GET['id'];
$stmt = $db->prepare("SELECT * FROM eventos WHERE id = ?");
$stmt->execute([$id]);
$evento = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'evento' => $evento]);