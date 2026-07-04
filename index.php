<?php
// index.php
require_once 'config.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // NOTA: Para producción usa password_verify($password, $user['password'])
        // Si las guardas en texto plano temporalmente, usa: $password === $user['password']
        // if ($user && password_verify($password, $user['password'])) {
        if ($user && $password === $user['password']) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre_completo'];
            $_SESSION['usuario_rol'] = $user['rol'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Credenciales incorrectas.';
        }
    } else {
        $error = 'Por favor, llena todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Sistema de Contenidos</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        /* Registro de tus colores personalizados en Tailwind v4 */
        @theme {
            --color-brand-pink: #e20064;
            --color-brand-yellow: #ffc111;
        }
    </style>
</head>
<body class="bg-black flex items-center justify-center min-h-screen p-4 sm:p-6">
    
    <div class="bg-slate-900 p-6 sm:p-8 rounded-xl shadow-2xl w-full max-w-md border border-slate-800/80 my-auto">
        
        <div class="flex justify-center mb-6">
            <img src="images/nexvor_logo.png" alt="Nexvor Logo" class="h-24 sm:h-32 w-auto object-contain max-w-full">
        </div>

        <h2 class="text-xl font-bold text-white text-center mb-6 tracking-tight">Iniciar Sesión</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-3 rounded-lg mb-4 text-xs sm:text-sm text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-slate-400 text-xs sm:text-sm font-medium mb-1.5">Correo Electrónico</label>
                <input type="email" name="email" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3 text-base sm:text-sm text-white focus:outline-none focus:border-[#ffc111] transition-colors appearance-none">
            </div>
            <div>
                <label class="block text-slate-400 text-xs sm:text-sm font-medium mb-1.5">Contraseña</label>
                <input type="password" name="password" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3 text-base sm:text-sm text-white focus:outline-none focus:border-[#ffc111] transition-colors appearance-none">
            </div>
            
            <button type="submit" class="w-full bg-[#e20064] hover:bg-[#e20064]/90 text-white font-bold py-3 px-4 rounded-lg transition duration-200 mt-2 cursor-pointer shadow-lg shadow-brand-pink/20 active:scale-[0.99] transform text-sm sm:text-base">
                Ingresar
            </button>
        </form>
    </div>

</body>
</html>