<?php
// dashboard.php
require_once 'config.php';
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}
$es_admin = ($_SESSION['usuario_rol'] === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard en Tiempo Real</title>
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
            <div class="flex items-center gap-3 ml-[25px]">
                <img src="images/logo_nexvor_n.png" alt="Nexvor Logo" class="h-10 w-auto object-contain">
                <span class="font-bold text-sm sm:text-base tracking-wide border-l border-slate-700 pl-3 hidden xs:inline text-slate-300">
                    Conexa Capital Central
                </span>
            </div>
            <div class="flex flex-wrap items-center justify-center sm:justify-end gap-3 sm:gap-4 w-full sm:w-auto">
                <span class="text-slate-400 text-xs sm:text-sm bg-slate-950 px-3 py-1.5 rounded-md border border-slate-800">
                    Hola, <b class="text-white"><?= $_SESSION['usuario_nombre'] ?></b> 
                    <span class="text-[10px] bg-[#ffc111]/10 text-[#ffc111] border border-[#ffc111]/20 px-1.5 py-0.5 rounded ml-1 font-mono uppercase"><?= $_SESSION['usuario_rol'] ?></span>
                </span>

                <?php if ($es_admin): ?>
                    <button onclick="exportarExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-xs sm:text-sm font-medium transition cursor-pointer shadow-lg shadow-emerald-900/30 flex items-center gap-1.5">
                        <span>📊</span> Exportar Excel
                    </button>
                <?php endif; ?>

                <a href="formulario.php" class="bg-[#e20064] hover:bg-[#e20064]/90 text-white px-3 py-1.5 rounded text-xs sm:text-sm font-medium transition cursor-pointer shadow-lg shadow-[#e20064]/15">
                    Subir Contenido
                </a>
                <a href="logout.php" class="text-slate-400 hover:text-red-400 transition text-xs sm:text-sm font-medium">
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="p-4 sm:p-6 max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Monitoreo de Contenidos</h2>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> 
                Tabla de Contenido Activa 24/7
            </div>
        </div>

        <div class="hidden md:block overflow-x-auto bg-slate-900 rounded-xl shadow-2xl border border-slate-800">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950 text-slate-400 uppercase text-[11px] tracking-wider border-b border-slate-800">
                        <th class="p-4">ID</th>
                        <th class="p-4">Fecha</th>
                        <th class="p-4">Creador</th>
                        <th class="p-4">Servicio</th>
                        <th class="p-4">Tipo/Red</th>
                        <th class="p-4">Código</th>
                        <th class="p-4">Empresa</th>
                        <th class="p-4 w-1/4">Copy</th>
                        <th class="p-4">Archivo</th>
                        <th class="p-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody id="tabla-contenidos" class="divide-y divide-slate-800/60 text-sm text-slate-300">
                </tbody>
            </table>
        </div>

        <div id="tarjetas-contenidos" class="grid grid-cols-1 gap-4 md:hidden">
        </div>
    </div>

    <div class="py-[10px]">
        <h1 class="text-[13px] text-center text-slate-500">Todos los Derechos Reservados - @ Sistemas Conexa 2026</h1>
    </div>

<script>
    const esAdmin = <?= $es_admin ? 'true' : 'false' ?>;
    const evtSource = new EventSource("api/stream.php");

    const copiesExpandidos = {};
    let ultimosContenidos = [];

    evtSource.onmessage = function(event) {
        ultimosContenidos = JSON.parse(event.data);
        renderizarContenidos(ultimosContenidos);
    };

    function renderizarContenidos(contenidos) {
        const tbody = document.getElementById("tabla-contenidos");
        const gridTarjetas = document.getElementById("tarjetas-contenidos");
        
        tbody.innerHTML = ""; 
        gridTarjetas.innerHTML = "";

        if(contenidos.length === 0) {
            const noDataHtml = `<div class="text-center text-slate-500 p-8 text-sm">No hay contenidos registrados.</div>`;
            gridTarjetas.innerHTML = noDataHtml;
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-slate-500 p-8">No hay contenidos registrados.</td></tr>`;
            return;
        }

        contenidos.forEach(item => {
            let servicioMostrar = item.servicio;
            if(item.servicio === 'OTROS' && item.servicio_especificar) {
                servicioMostrar += ` (${item.servicio_especificar})`;
            }

            let estadoHtml = '';
            if (esAdmin) {
                estadoHtml = `
                    <select onchange="cambiarEstado(${item.id}, this.value)" class="bg-black border border-slate-700 rounded text-xs p-1.5 text-white focus:outline-none focus:border-[#ffc111] transition w-full md:w-auto">
                        <option value="ENTREGADO" ${item.estado === 'ENTREGADO' ? 'selected' : ''}>📥 ENTREGADO</option>   
                        <option value="DESCARGADO" ${item.estado === 'DESCARGADO' ? 'selected' : ''}>⏳ DESCARGADO</option>
                        <option value="PUBLICADO" ${item.estado === 'PUBLICADO' ? 'selected' : ''}>✅ PUBLICADO</option>
                    </select>
                `;
            } else {
                // Colores adaptados a los nuevos estados
                let badgeColor = "bg-blue-500/10 text-blue-400 border border-blue-500/20"; // ENTREGADO (Azul)
                if(item.estado === 'DESCARGADO') badgeColor = "bg-[#ffc111]/10 text-[#ffc111] border border-[#ffc111]/20"; // DESCARGADO (Amarillo)
                if(item.estado === 'PUBLICADO') badgeColor = "bg-green-500/10 text-green-400 border border-green-500/20"; // PUBLICADO (Verde)
                
                estadoHtml = `<span class="px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide ${badgeColor} inline-block">${item.estado}</span>`;
            }

            const limiteTexto = 80;
            const textoCompleto = item.copy || '-';
            let copyHtml = textoCompleto;

            if (textoCompleto.length > limiteTexto) {
                const estaExpandido = copiesExpandidos[item.id] || false;
                if (estaExpandido) {
                    copyHtml = `
                        <span class="whitespace-pre-line">${textoCompleto}</span>
                        <button onclick="toggleCopy(${item.id})" class="text-[#ffc111] hover:underline text-xs block mt-1 font-medium cursor-pointer">Ver menos</button>
                    `;
                } else {
                    copyHtml = `
                        <span>${textoCompleto.substring(0, limiteTexto)}...</span>
                        <button onclick="toggleCopy(${item.id})" class="text-[#e20064] hover:underline text-xs block mt-1 font-medium cursor-pointer">Ver más</button>
                    `;
                }
            } else {
                copyHtml = `<span class="whitespace-pre-line">${textoCompleto}</span>`;
            }

            // --- 1. TABLA (ESCRITORIO) ---
            const tr = document.createElement("tr");
            tr.className = "hover:bg-slate-800/40 transition duration-150 group";
            tr.innerHTML = `
                <td class="p-4 font-mono text-xs text-slate-500 group-hover:text-slate-400">${item.id}</td>
                <td class="p-4 text-xs text-slate-400">${item.fecha_subida}</td>
                <td class="p-4 font-medium text-white">${item.creador}</td> <td class="p-4"><span class="bg-slate-950 text-slate-300 border border-slate-800 px-2.5 py-1 rounded text-xs font-medium">${servicioMostrar}</span></td>
                <td class="p-4 text-xs text-slate-400">${item.tipo_contenido} <span class="text-slate-600">/</span> <span class="text-[#e20064]/80 ">${item.red_social}</span></td>
                <td class="p-4 font-mono text-xs text-slate-400">${item.codigo_archivo}</td>
                <td class="p-4"><span class="bg-blue-500/10 text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded text-xs font-semibold tracking-wide uppercase">${item.empresa || '-'}</span></td>
                <td class="p-4 text-xs text-slate-300 break-words">${copyHtml}</td>
                <td class="p-4">
                    <a href="${item.url_archivo}" target="_blank" class="text-[#e20064] hover:text-[#e20064]/80 inline-flex items-center gap-1 text-xs font-medium transition">
                        Ver archivo 📁
                    </a>
                </td>
                <td class="p-4 text-center">${estadoHtml}</td>
            `;
            tbody.appendChild(tr);

            // --- 2. TARJETA (CELULAR) ---
            const card = document.createElement("div");
            card.className = "bg-slate-900 border border-slate-800 p-4 rounded-xl space-y-3 shadow-lg";
            card.innerHTML = `
                <div class="flex justify-between items-start border-b border-slate-800 pb-2">
                    <div>
                        <span class="text-[10px] font-mono text-slate-500 block">ID: #${item.id} - ${item.fecha_subida}</span>
                        <span class="font-bold text-white text-base">${item.creador}</span>
                    </div>
                    <div class="text-right">${estadoHtml}</div>
                </div>
                
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase">Empresa / Marca</span>
                        <span class="text-blue-400 font-bold tracking-wide uppercase text-xs">${item.empresa || '-'}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase">Servicio</span>
                        <span class="text-slate-200 font-medium">${servicioMostrar}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase">Plataforma</span>
                        <span class="text-slate-200 font-medium">${item.tipo_contenido} (${item.red_social})</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase">Código de Archivo</span>
                        <span class="font-mono text-[#ffc111] text-xs">${item.codigo_archivo}</span>
                    </div>
                </div>

                <div class="bg-slate-950 p-3 rounded-lg border border-slate-800/80 text-xs text-slate-300 break-words">
                    <span class="text-slate-500 block text-[9px] uppercase mb-1 font-mono">Copy / Descripción</span>
                    ${copyHtml}
                </div>

                <div class="pt-1">
                    <a href="${item.url_archivo}" target="_blank" class="w-full bg-slate-800 hover:bg-slate-700 text-slate-200 text-center block py-2 rounded-lg text-xs font-medium transition border border-slate-700/50">
                        Ver o Descargar Archivo Adjunto 📁
                    </a>
                </div>
            `;
            gridTarjetas.appendChild(card);
        });
    }

    function toggleCopy(id) {
        copiesExpandidos[id] = !copiesExpandidos[id];
        renderizarContenidos(ultimosContenidos);
    }

    function cambiarEstado(id, nuevoEstado) {
        fetch('api/cambiar_estado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&estado=${nuevoEstado}`
        })
        .then(res => res.text())
        .then(data => {
            if(data !== 'ok') alert('Error al cambiar estado');
        });
    }

    function exportarExcel() {
        if (!esAdmin || ultimosContenidos.length === 0) {
            alert("No hay datos disponibles para exportar.");
            return;
        }

        const columnas = ["ID", "Fecha Subida", "Creador", "Servicio", "Servicio Especificado", "Tipo Contenido", "Red Social", "Código Archivo", "Empresa", "URL Archivo", "Estado"];
        
        const filas = ultimosContenidos.map(item => [
            item.id,
            item.fecha_subida,
            `"${(item.creador || '').replace(/"/g, '""')}"`,
            `"${(item.servicio || '').replace(/"/g, '""')}"`,
            `"${(item.servicio_especificar || '').replace(/"/g, '""')}"`,
            `"${(item.tipo_contenido || '').replace(/"/g, '""')}"`,
            `"${(item.red_social || '').replace(/"/g, '""')}"`,
            `"${(item.codigo_archivo || '').replace(/"/g, '""')}"`,
            `"${(item.empresa || '').replace(/"/g, '""')}"`,
            `"${(item.url_archivo || '').replace(/"/g, '""')}"`,
            `"${(item.estado || '').replace(/"/g, '""')}"`
        ]);

        const contenidoCsv = [columnas.join(";"), ...filas.map(f => f.join(";"))].join("\r\n");
        
        const blob = new Blob([new Uint8Array([0xEF, 0xBB, 0xBF]), contenidoCsv], { type: "text/csv;charset=utf-8;" });
        
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        
        const fechaHoy = new Date().toISOString().split('T')[0];
        link.setAttribute("href", url);
        link.setAttribute("download", `Reporte_Contenidos_Conexa_${fechaHoy}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
</body>
</html>