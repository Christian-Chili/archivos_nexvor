<?php
// formulario.php
require_once 'config.php';
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Contenido</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="icon" type="image/png" sizes="32x32" href="images/nexvor_logo_n.png">
    <link rel="apple-touch-icon" sizes="192x192" href="images/nexvor_logo_n.png">
    <style>
        @theme {
            --color-brand-pink: #e20064;
            --color-brand-yellow: #ffc111;
        }
    </style>
</head>
<body class="bg-black text-slate-100 min-h-screen font-sans">
    
    <nav class="bg-slate-900 border-b border-slate-800 p-4 sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <img src="images/logo_nexvor_n.png" alt="Nexvor Logo" class="h-10 w-auto object-contain">
                <span class="font-bold text-sm sm:text-base tracking-wide border-l border-slate-700 pl-3 hidden xs:inline text-slate-300">
                    Panel Interno
                </span>
            </div>
            <div class="flex items-center gap-4 w-full sm:w-auto justify-center sm:justify-end">
                <a href="dashboard.php" class="text-slate-400 hover:text-white text-xs sm:text-sm font-medium transition">
                    Ver Dashboard
                </a>
                <a href="logout.php" class="text-slate-400 hover:text-red-400 text-xs sm:text-sm font-medium transition">
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto my-6 sm:mt-10 p-5 sm:p-8 bg-slate-900 rounded-xl shadow-2xl border border-slate-800/80">
        <h2 class="text-xl sm:text-2xl font-bold mb-6 text-white border-b border-slate-800 pb-3 tracking-tight">
            Subir Nuevo Contenido
        </h2>
        
        <form id="uploadForm" action="api/subir_contenido.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            
            <div>
                <label class="block text-xs sm:text-sm font-medium text-slate-400 mb-1">Creador (Usuario Activo)</label>
                <input type="text" value="<?= $_SESSION['usuario_nombre'] ?>" disabled class="w-full bg-slate-950 border border-slate-800 rounded p-2.5 text-slate-500 cursor-not-allowed text-sm">
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-slate-300 mb-1">Servicio</label>
                <select name="servicio" id="servicio" required class="w-full bg-slate-800 border border-slate-700 rounded p-2.5 text-white focus:outline-none focus:border-[#ffc111] transition text-sm cursor-pointer" onchange="toggleEspecificar(this.value)">
                    <option value="PPF">PPF</option>
                    <option value="TAPICERIA">TAPICERIA</option>
                    <option value="LAMINADOS">LAMINADOS</option>
                    <option value="DETAILING">DETAILING</option>
                    <option value="CERAMICO">CERAMICO</option>
                    <option value="OTROS">OTROS</option>
                </select>
            </div>

            <div id="div-especificar" class="hidden transition-all">
                <label class="block text-xs sm:text-sm font-medium text-slate-300 mb-1">Especifique el Servicio</label>
                <input type="text" name="servicio_especificar" id="servicio_especificar" class="w-full bg-slate-800 border border-slate-700 rounded p-2.5 text-white focus:outline-none focus:border-[#ffc111] transition text-sm">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-slate-300 mb-1">Tipo de Contenido</label>
                    <select name="tipo_contenido" required class="w-full bg-slate-800 border border-slate-700 rounded p-2.5 text-white focus:outline-none focus:border-[#ffc111] transition text-sm cursor-pointer">
                        <option value="VIDEO">VIDEO</option>
                        <option value="FLYER">FLYER</option>
                        <option value="POST">POST</option>
                        <option value="OTRO">OTRO</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-slate-300 mb-1">Red Social</label>
                    <input type="text" name="red_social" placeholder="Ej: Instagram, TikTok" required class="w-full bg-slate-800 border border-slate-700 rounded p-2.5 text-white focus:outline-none focus:border-[#ffc111] transition text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-slate-300 mb-1">Código de Archivo / Identificador</label>
                <input type="text" name="codigo_archivo" placeholder="Ej: v_ppf_01" required class="w-full bg-slate-800 border border-slate-700 rounded p-2.5 text-white focus:outline-none focus:border-[#ffc111] transition font-mono text-sm">
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-slate-300 mb-1">Empresa / Marca</label>
                <select name="empresa" required class="w-full bg-slate-800 border border-slate-700 rounded p-2.5 text-white focus:outline-none focus:border-[#ffc111] transition text-sm cursor-pointer">
                    <option value="" disabled selected>Selecciona una empresa...</option>
                    <option value="NEXVOR">NEXVOR</option>
                    <option value="KORAX">KORAX</option>
                    <option value="ALIAGA GLOBAL">ALIAGA GLOBAL</option>
                    <option value="GRUPO POLAR">GRUPO POLAR</option>
                    <option value="VORAX">VORAX</option>
                    <option value="FORTEXIA">FORTEXIA</option>
                    <option value="AUTOTECH">AUTOTECH</option>
                    <option value="AXIONA KAPITAL">AXIONA KAPITAL</option>
                    <option value="SEMILLA DORADA">SEMILLA DORADA</option>
                </select>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-slate-300 mb-1">Copy (Texto y Emojis)</label>
                <textarea name="copy" rows="4" placeholder="Escribe aquí el copy del post... 🔥" class="w-full bg-slate-800 border border-slate-700 rounded p-2.5 text-white focus:outline-none focus:border-[#ffc111] transition text-sm"></textarea>
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-medium text-slate-300 mb-1">Archivo (Máximo 100 MB)</label>
                <input type="file" id="archivo" name="archivo" required class="w-full bg-slate-800 border border-slate-700 rounded p-2 text-white text-xs sm:text-sm file:mr-4 file:py-1.5 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-[#e20064] file:text-white hover:file:bg-[#e20064]/90 file:transition file:cursor-pointer">
                <p id="error-peso" class="text-red-400 text-xs mt-1.5 hidden">⚠️ El archivo excede el límite permitido de 100MB.</p>
            </div>

            <button type="submit" id="btn-subir" class="w-full bg-[#e20064] hover:bg-[#e20064]/90 text-white font-bold py-3 rounded-lg transition duration-200 shadow-lg shadow-[#e20064]/20 cursor-pointer text-sm sm:text-base mt-2">
                Subir Contenido
            </button>

            <div id="texto-carga" class="hidden text-center text-[#ffc111] text-xs sm:text-sm font-medium animate-pulse mt-3 bg-[#ffc111]/10 border border-[#ffc111]/20 p-3 rounded-lg">
                ⏳ Subiendo archivo pesado... Por favor no cierres la página (Puede tardar unos minutos).
            </div>
        </form>

    </div>
    <div class="py-[10px]">
        <h1 class="text-[13px] text-center text-slate-500">Todos los Derechos Reservados - @ Sistemas Conexa 2026</h1>
    </div>

    <script>
        function toggleEspecificar(val) {
            const div = document.getElementById('div-especificar');
            const input = document.getElementById('servicio_especificar');
            if(val === 'OTROS') {
                div.classList.remove('hidden');
                input.setAttribute('required', 'required');
                input.focus();
            } else {
                div.classList.add('hidden');
                input.removeAttribute('required');
            }
        }

        // Validación de Peso y Activación de Loader Visual
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('archivo');
            const btnSubir = document.getElementById('btn-subir');
            const textoCarga = document.getElementById('texto-carga');
            const errorPeso = document.getElementById('error-peso');

            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size; 
                const maxSize = 100 * 1024 * 1024; // 100 MB
                
                if (fileSize > maxSize) {
                    e.preventDefault();
                    errorPeso.classList.remove('hidden');
                    alert('El archivo supera los 100MB permitidos.');
                    return;
                }
            }
            
            // Si pasa la validación, ocultamos error, deshabilitamos botón y mostramos aviso de carga
            errorPeso.classList.add('hidden');
            btnSubir.disabled = true;
            btnSubir.classList.add('opacity-50', 'cursor-not-allowed');
            btnSubir.innerText = 'Procesando Envío...';
            textoCarga.classList.remove('hidden');
        });
    </script>
</body>
</html>