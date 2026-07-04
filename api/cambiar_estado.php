<?php
// api/cambiar_estado.php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'No autorizado';
    exit;
}

$id = intval($_POST['id']);
$estado = $_POST['estado'];

// Lista de estados permitidos por seguridad
if (in_array($estado, ['NO PUBLICADO', 'EN CURSO', 'PUBLICADO'])) {
    $stmt = $pdo->prepare("UPDATE contenidos SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);
    echo 'ok';
} else {
    echo 'error';
}
?>