<?php
// api/exportar_excel.php
require_once '../config.php';

// Validar sesión y rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("HTTP/1.1 03 Forbidden");
    exit('Acceso denegado.');
}

// Obtener los datos más recientes de la base de datos
$stmt = $pdo->query('SELECT * FROM contenidos ORDER BY id DESC'); // Ajusta 'usuarios_contenidos' al nombre real de tu tabla
$contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fechaHoy = date('Y-m-d');
$filename = "Reporte_Contenidos_Conexa_" . $fechaHoy . ".xls";

// Cabeceras HTTP para forzar la descarga en formato Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false);

// Inyectar el BOM UTF-8 para evitar caracteres extraños con las tildes y eñes
echo "\xEF\xBB\xBF";
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    /* Estilos profesionales optimizados para la renderización interna de Microsoft Excel */
    .table-report {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }
    .title-header {
        font-size: 16pt;
        font-weight: bold;
        color: #FFFFFF;
        background-color: #000000;
        text-align: left;
        padding: 15px;
    }
    .subtitle-header {
        font-size: 10pt;
        color: #A0AEC0;
        background-color: #1A202C;
        padding: 8px;
    }
    .th-main {
        background-color: #e20064; /* Tu color Rosa Corporativo */
        color: #FFFFFF;
        font-size: 11pt;
        font-weight: bold;
        text-align: center;
        border: 1px solid #CBD5E0;
        padding: 10px;
    }
    .td-cell {
        font-size: 10pt;
        border: 1px solid #E2E8F0;
        padding: 8px;
        vertical-align: middle;
    }
    .td-id {
        font-family: 'Courier New', monospace;
        background-color: #F7FAFC;
        text-align: center;
        font-weight: bold;
    }
    .td-fecha {
        text-align: center;
        color: #4A5568;
    }
    .td-badge-servicio {
        background-color: #F0F4F8;
        color: #102A43;
        font-weight: bold;
        text-align: center;
    }
    .td-badge-estado {
        font-weight: bold;
        text-align: center;
    }
    /* Colores pro basados en los estados de tu sistema */
    .estado-publicado {
        background-color: #C6F6D5;
        color: #22543D;
    }
    .estado-en-curso {
        background-color: #FEFCBF;
        color: #744210;
        border: 1px solid #ffc111; /* Tu color amarillo */
    }
    .estado-no-publicado {
        background-color: #FED7D7;
        color: #742A2A;
    }
</style>
</head>
<body>

<table class="table-report">
    <thead>
        <tr>
            <th colspan="10" class="title-header">CONEXA CAPITAL CENTRAL — REPORTE DE CONTENIDOS</th>
        </tr>
        <tr>
            <th colspan="10" class="subtitle-header">Generado por el Administrador el: <?= date('d/m/Y H:i:s') ?> | Sistema Nexvor</th>
        </tr>
        <tr>
            <th colspan="10" style="background-color: #ffc111; height: 4px;"></th> </tr>
        <tr></tr>
        
        <tr>
            <th class="th-main">ID</th>
            <th class="th-main">Fecha de Subida</th>
            <th class="th-main">Creador / Usuario</th>
            <th class="th-main">Servicio</th>
            <th class="th-main">Tipo Contenido</th>
            <th class="th-main">Red Social</th>
            <th class="th-main">Código Archivo</th>
            <th class="th-main">Copy / Descripción</th>
            <th class="th-main">Enlace de Archivo</th>
            <th class="th-main">Estado Actual</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($contenidos as $item): 
            // Formatear el servicio a mostrar
            $servicioMostrar = $item['servicio'];
            if($item['servicio'] === 'OTROS' && !empty($item['servicio_especificar'])) {
                $servicioMostrar .= " (" . $item['servicio_especificar'] . ")";
            }

            // Asignar clases de color según el estado
            $claseEstado = 'estado-no-publicado';
            if ($item['estado'] === 'EN CURSO') $claseEstado = 'estado-en-curso';
            if ($item['estado'] === 'PUBLICADO') $claseEstado = 'estado-published';
        ?>
            <tr>
                <td class="td-cell td-id"><?= $item['id'] ?></td>
                <td class="td-cell td-fecha"><?= $item['fecha_subida'] ?></td>
                <td class="td-cell" style="font-weight: 500;"><?= htmlspecialchars($item['creador']) ?></td>
                <td class="td-cell td-badge-servicio"><?= htmlspecialchars($servicioMostrar) ?></td>
                <td class="td-cell" style="text-align: center;"><?= htmlspecialchars($item['tipo_contenido']) ?></td>
                <td class="td-cell" style="text-align: center; color: #e20064; font-weight: 500;"><?= htmlspecialchars($item['red_social']) ?></td>
                <td class="td-cell" style="font-family: monospace; color: #4A5568;"><?= htmlspecialchars($item['codigo_archivo']) ?></td>
                <td class="td-cell" style="font-style: italic; max-width: 300px;"><?= htmlspecialchars($item['copy'] ?? '-') ?></td>
                <td class="td-cell" style="text-align: center;">
                    <a href="<?= htmlspecialchars($item['url_archivo']) ?>" style="color: #3182CE; text-decoration: underline;">Abrir Adjunto 📁</a>
                </td>
                <td class="td-cell td-badge-estado <?= $claseEstado ?>"><?= $item['estado'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>