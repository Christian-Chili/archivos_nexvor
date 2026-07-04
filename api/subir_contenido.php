<?php
// api/subir_contenido.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
// Capturamos el nombre del creador desde la sesión para cumplir con la restricción NOT NULL de la DB
$creador = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Usuario Nexvor';

// --- CAMBIO: CAPTURA DE NUEVO CAMPO EMPRESA ---
$empresa = isset($_POST['empresa']) ? trim($_POST['empresa']) : null;

$servicio = $_POST['servicio'];
$servicio_especificar = ($servicio === 'OTROS') ? $_POST['servicio_especificar'] : null;
$tipo_contenido = $_POST['tipo_contenido'];
$red_social = trim($_POST['red_social']);
$codigo_archivo = trim($_POST['codigo_archivo']);
$copy = $_POST['copy'];

if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['archivo']['tmp_name'];
    $fileName = $_FILES['archivo']['name'];
    $fileSize = $_FILES['archivo']['size'];
    
    // Validación Backend de 100MB
    if ($fileSize > (100 * 1024 * 1024)) {
        die("Error: El archivo supera los 100MB.");
    }

    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    // Generamos un nombre único para evitar duplicados en la carpeta
    $newFileName = time() . '_' . $codigo_archivo . '.' . $fileExtension;
    
    // --- SOLUCIÓN DE PERMISOS: RUTAS ABSOLUTAS FÍSICAS EN EL DISCO ---
    $uploadFileDir = dirname(__DIR__) . '/uploads/';
    
    // Si no existe la carpeta, la crea con permisos 0755
    if(!is_dir($uploadFileDir)){
        mkdir($uploadFileDir, 0755, true);
    } else {
        // Si ya existe, forzamos la actualización de permisos en caliente
        chmod($uploadFileDir, 0755); 
    }

    $dest_path = $uploadFileDir . $newFileName;

    // Intentamos mover el archivo usando la ruta absoluta del sistema de archivos
    if(move_uploaded_file($fileTmpPath, $dest_path)) {
        
        // Guardamos solo la ruta corta/relativa en la base de datos para el frontend
        $url_archivo = 'uploads/' . $newFileName;

        // --- CONSULTA MODIFICADA: Se añade la columna y el parámetro para 'empresa' ---
        $sql = "INSERT INTO usuarios_contenidos (usuario_id, creador, empresa, servicio, servicio_especificar, tipo_contenido, red_social, codigo_archivo, copy, url_archivo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $usuario_id, 
            $creador, 
            $empresa, // <-- PARAMETRO DE EMPRESA INYECTADO AQUÍ
            $servicio, 
            $servicio_especificar, 
            $tipo_contenido, 
            $red_social, 
            $codigo_archivo, 
            $copy, 
            $url_archivo
        ]);

        header("Location: ../dashboard.php?success=1");
        exit;
    } else {
        // --- BLOQUE DE DIAGNÓSTICO EN VIVO ---
        echo "<div style='font-family:sans-serif; background:#111; color:#eee; padding:20px; border-radius:8px; max-width:600px; margin:20px auto; border:1px solid #cc0000;'>";
        echo "<h3 style='color:#ff3333; margin-top:0;'>⚠️ Error al procesar el archivo en el Servidor</h3>";
        echo "<p>El código PHP se ejecutó bien, pero el sistema de archivos denegó la operación.</p><hr style='border-color:#333;'>";
        echo "<b>Ruta donde se intentó guardar:</b> <code style='background:#222; padding:2px 4px; color:#ffc111;'>" . htmlspecialchars($dest_path) . "</code><br><br>";
        echo "<b>¿La carpeta destino existe?:</b> " . (is_dir($uploadFileDir) ? '<span style="color:#00ff00;">SÍ</span>' : '<span style="color:#ff3333;">NO</span>') . "<br>";
        echo "<b>¿PHP tiene permiso de ESCRITURA en esa carpeta?:</b> " . (is_writable($uploadFileDir) ? '<span style="color:#00ff00;">SÍ</span>' : '<span style="color:#ff3333;">NO (Bloqueado por el Hosting)</span>') . "<br>";
        echo "<b>¿El archivo temporal original subió a la memoria?:</b> " . (file_exists($fileTmpPath) ? '<span style="color:#00ff00;">SÍ</span>' : '<span style="color:#ff3333;">NO (El hosting lo rechazó antes por peso)</span>') . "<br>";
        echo "</div>";
        die();
    }
} else {
    echo "Error en la subida del archivo. Código de error del motor PHP: " . $_FILES['archivo']['error'];
}
?>