<?php
// api/stream.php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

require_once '../config.php';

// CERRAR LA SESIÓN PARA EL TRÁFICO: Esto evita que se congele el resto de la web
session_write_close(); 

while (true) {
    $sql = "SELECT c.*, u.nombre_completo as creador 
            FROM contenidos c 
            JOIN usuarios u ON c.usuario_id = u.id 
            ORDER BY c.id DESC";
    
    $stmt = $pdo->query($sql);
    $contenidos = $stmt->fetchAll();

    echo "data: " . json_encode($contenidos) . "\n\n";
    
    ob_flush();
    flush();

    // Sube esto a 3 o 4 segundos para darle un respiro a Hostinger
    sleep(3); 
}
?>