<!DOCTYPE html>
<html lang="es" class="h-full bg-[#f8f7fa] dark:bg-[#161d31]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KFC Venezuela - Dashboard de Métricas de Personal (Vuexy Style)</title>
    
    <!-- Fonts & CDNs -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Tailwind Theme Settings (Vuexy palette) -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#dc2626',      // KFC Crimson Red
                            primaryLight: '#fef2f2',
                            secondary: '#82868b',
                            success: '#28c76f',
                            successLight: '#ddf6e8',
                            danger: '#ea5455',
                            dangerLight: '#fceaea',
                            warning: '#ff9f43',
                            warningLight: '#fff0e1',
                            info: '#00cfe8',
                            infoLight: '#e0f9fc',
                            dark: '#4b4b4b',
                            vuexyBg: '#f8f7fa',
                            vuexyDarkBg: '#161d31',
                            vuexyCard: '#ffffff',
                            vuexyDarkCard: '#242b3d',
                            vuexyBorder: '#dbdade',
                            vuexyDarkBorder: '#404656',
                        }
                    },
                    fontFamily: {
                        sans: ['Public Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Public Sans', sans-serif;
            background-color: #f8f7fa;
        }
        .dark body {
            background-color: #161d31;
        }
        
        /* Vuexy Sidebar Styling */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 30;
            background-color: #ffffff;
            border-right: 1px solid #dbdade;
            transition: all 0.3s;
        }
        .dark .sidebar {
            background-color: #242b3d;
            border-right: 1px solid #404656;
        }

        /* Vuexy Floating Navbar */
        .navbar-floating {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.08);
            border-radius: 10px;
        }
        .dark .navbar-floating {
            background: rgba(36, 43, 61, 0.9);
            box-shadow: 0 4px 24px 0 rgba(0, 0, 0, 0.25);
        }

        /* Vuexy Card Styling */
        .vuexy-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 18px 0 rgba(15, 23, 42, 0.05);
            border: 1px solid rgba(219, 218, 222, 0.4);
            transition: all 0.3s ease;
        }
        .dark .vuexy-card {
            background-color: #242b3d;
            border: 1px solid rgba(64, 70, 86, 0.4);
            box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.2);
        }
        .vuexy-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px 0 rgba(15, 23, 42, 0.08);
        }

        /* Active Navigation Menu Link */
        .menu-link-active {
            background: linear-gradient(72.47deg, #dc2626 22.16%, rgba(220, 38, 38, 0.7) 76.47%) !important;
            box-shadow: 0px 2px 6px rgba(220, 38, 38, 0.3) !important;
            color: #ffffff !important;
        }
        
        /* KFC Brand Stripes */
        .brand-stripes {
            background: repeating-linear-gradient(
                90deg,
                #dc2626,
                #dc2626 10px,
                #ffffff 10px,
                #ffffff 20px
            );
            height: 4px;
        }
    </style>
</head>
<body class="h-full antialiased overflow-x-hidden">

    <!-- 1. LEFT SIDEBAR (Vuexy Fixed Navigation) -->
    <aside class="sidebar hidden lg:flex flex-col justify-between">
        <div>
            <!-- Brand Header -->
            <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🐔</span>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xl font-extrabold tracking-tighter text-slate-800 dark:text-white">KFC</span>
                            <span class="text-[9px] uppercase bg-brand-primary text-white font-extrabold px-1 rounded tracking-wider">Metrics</span>
                        </div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block leading-none">Venezuela</span>
                    </div>
                </div>
            </div>

            <!-- Brand Stripes Accent -->
            <div class="brand-stripes"></div>

            <!-- Navigation Links -->
            <nav class="px-4 py-6 space-y-1">
                <a href="/" class="menu-link-active flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-semibold transition-all">
                    <span>📊</span>
                    <span>Dashboard / RRHH</span>
                </a>
                
                <a href="/admin" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <span>⚙️</span>
                    <span>Consola del Admin</span>
                </a>

                <button onclick="document.getElementById('uploader-modal').classList.remove('hidden')" class="w-full flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all text-left">
                    <span>📂</span>
                    <span>Importar Excel</span>
                </button>

                <div class="pt-4 pb-2 px-4 text-[10px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider">
                    Recursos del Panel
                </div>

                <a href="/admin/tiendas" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <span>🏬</span>
                    <span>Sucursales (CDC)</span>
                </a>
                
                <a href="/admin/empleados" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <span>👥</span>
                    <span>Colaboradores</span>
                </a>

                <a href="/admin/contratos" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <span>📝</span>
                    <span>Historial de Contratos</span>
                </a>
            </nav>
        </div>

        <!-- Footer brand -->
        <div class="p-6 border-t border-gray-100 dark:border-slate-800 text-[10px] text-gray-400 font-semibold text-center">
            &copy; {{ date('Y') }} KFC Venezuela v1.0
        </div>
    </aside>

    <!-- 2. MAIN CONTENT AREA (Offset by 260px on Desktop) -->
    <div class="lg:pl-[260px] flex flex-col min-h-screen">
        
        <!-- Floating Navbar -->
        <div class="px-6 pt-5">
            <header class="navbar-floating w-full px-6 py-4 flex items-center justify-between z-20">
                <!-- Breadcrumbs -->
                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                    <span class="text-gray-400">Dashboard</span>
                    <span>/</span>
                    <span class="text-gray-400">RRHH</span>
                    <span>/</span>
                    <span class="text-slate-800 dark:text-white font-bold">Métricas de Personal</span>
                </div>

                <!-- Right items -->
                <div class="flex items-center gap-4">
                    <button onclick="document.documentElement.classList.toggle('dark')" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full text-lg leading-none" title="Cambiar Tema">
                        🌓
                    </button>
                    <!-- User Info Profile -->
                    <div class="flex items-center gap-2 border-l border-gray-200 dark:border-slate-700 pl-4">
                        <div class="text-right leading-none hidden sm:block">
                            <span class="text-xs font-bold text-slate-800 dark:text-white block">Administrador KFC</span>
                            <span class="text-[9px] text-gray-400 uppercase font-semibold">Superusuario</span>
                        </div>
                        <span class="w-8 h-8 rounded-full bg-brand-primary text-white font-bold text-xs flex items-center justify-center border-2 border-white dark:border-slate-900 shadow">
                            AD
                        </span>
                    </div>
                </div>
            </header>
        </div>

        <!-- Main Body Content -->
        <div class="px-6 py-8 flex-grow space-y-6">

            <!-- Filter Panel -->
            <section class="vuexy-card p-6">
                <form method="GET" action="/" class="grid grid-cols-1 sm:grid-cols-4 items-end gap-5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Año</label>
                        <select name="anio" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-primary text-xs font-medium" onchange="this.form.submit()">
                            <option value="2023" {{ $anio == 2023 ? 'selected' : '' }}>2023</option>
                            <option value="2024" {{ $anio == 2024 ? 'selected' : '' }}>2024</option>
                            <option value="2025" {{ $anio == 2025 ? 'selected' : '' }}>2025</option>
                            <option value="2026" {{ $anio == 2026 ? 'selected' : '' }}>2026</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Mes</label>
                        <select name="mes" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-primary text-xs font-medium" onchange="this.form.submit()">
                            @foreach([1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'] as $mNum => $mName)
                                <option value="{{ $mNum }}" {{ $mes == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Sucursal (CDC)</label>
                        <select name="tienda_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-primary text-xs font-medium" onchange="this.form.submit()">
                            <option value="">Todas las Sucursales (Promedio Cadena)</option>
                            @foreach($tiendasList as $t)
                                <option value="{{ $t->id }}" {{ $tiendaId == $t->id ? 'selected' : '' }}>[{{ $t->codigo_corto }}] {{ $t->cdc }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </section>

            <!-- Dashboard Row 1: Congratulations Card (Left) & KPIs Stats (Right) -->
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Congratulations/Summary Card -->
                <div class="vuexy-card p-6 bg-gradient-to-tr from-slate-900 to-slate-800 border-none text-white relative overflow-hidden flex flex-col justify-between min-h-[220px]">
                    <div class="absolute top-0 right-0 p-4 opacity-10 text-9xl leading-none select-none">🐔</div>
                    <div>
                        <h3 class="text-lg font-bold">¡Felicidades KFC! 🎉</h3>
                        <p class="text-xs text-gray-300 mt-1">Este es el resumen acumulado YTD para el mes de <strong>{{ $mesNombre }} {{ $anio }}</strong>.</p>
                    </div>
                    <div class="my-4">
                        <span class="text-5xl font-extrabold font-mono text-yellow-400 leading-none">{{ $scoreTotalGente }}</span>
                        <span class="text-xs text-yellow-500 ml-1 font-bold">Puntos Score YTD</span>
                    </div>
                    <div>
                        <span class="text-[10px] bg-red-600 text-white font-bold uppercase tracking-wider px-3 py-1 rounded-full border border-red-500/20">
                            @if($tiendaSeleccionada && $localRank)
                                Sucursal Rank: N° {{ $localRank }} de {{ count($leaderboard ?? $podium) + 3 }}
                            @else
                                Promedio de la Cadena
                            @endif
                        </span>
                    </div>
                </div>

                <!-- KPIs Quick Stats Grid (2/3 width) -->
                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- KPI 1: Active Headcount -->
                    <div class="vuexy-card p-5 flex flex-col justify-between min-h-[100px] border-l-4 border-l-brand-primary">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Dotación</span>
                                <span class="text-2xl font-bold text-slate-800 dark:text-white mt-1 font-mono">{{ $activeHeadcount }}</span>
                            </div>
                            <span class="p-2 bg-brand-primaryLight dark:bg-red-950/20 text-brand-primary rounded-lg text-sm">👤</span>
                        </div>
                        <span class="text-[10px] text-gray-500 mt-2 block">Personal activo a fin de mes</span>
                    </div>

                    <!-- KPI 2: Rotacion -->
                    <div class="vuexy-card p-5 flex flex-col justify-between min-h-[100px] border-l-4 border-l-brand-warning">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Rotación</span>
                                <span class="text-2xl font-bold text-slate-800 dark:text-white mt-1 font-mono">{{ round($rotacionMtd * 100, 1) }}%</span>
                            </div>
                            <span class="p-2 bg-brand-warningLight dark:bg-yellow-950/20 text-brand-warning rounded-lg text-sm">🔄</span>
                        </div>
                        <span class="text-[10px] text-gray-500 mt-2 block">Acumulado YTD: <strong>{{ round($rotacionYtd * 100, 1) }}%</strong></span>
                    </div>

                    <!-- KPI 3: Retencion -->
                    <div class="vuexy-card p-5 flex flex-col justify-between min-h-[100px] border-l-4 border-l-brand-success">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Retención</span>
                                <span class="text-2xl font-bold text-slate-800 dark:text-white mt-1 font-mono">{{ round($retencionMtd * 100, 1) }}%</span>
                            </div>
                            <span class="p-2 bg-brand-successLight dark:bg-green-950/20 text-brand-success rounded-lg text-sm">🛡️</span>
                        </div>
                        <span class="text-[10px] text-gray-500 mt-2 block">Antigüedad >12m en bajas</span>
                    </div>

                    <!-- KPI 4: Productividad -->
                    <div class="vuexy-card p-5 flex flex-col justify-between min-h-[100px] border-l-4 border-l-brand-info">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Prod.</span>
                                <span class="text-2xl font-bold text-slate-800 dark:text-white mt-1 font-mono">{{ round($productividadLocal, 0) }}</span>
                            </div>
                            <span class="p-2 bg-brand-infoLight dark:bg-cyan-950/20 text-brand-info rounded-lg text-sm">⚡</span>
                        </div>
                        <span class="text-[10px] text-gray-500 mt-2 block">Transacciones / Gente</span>
                    </div>

                </div>

            </section>

            <!-- Dashboard Row 2: Charts Area -->
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Rotation Line Chart -->
                <div class="vuexy-card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300">
                            Tendencia de Rotación Mensual (%)
                        </h4>
                        <span class="text-[10px] text-gray-400">Año {{ $anio }}</span>
                    </div>
                    <div class="h-[260px]">
                        <canvas id="rotationChart"></canvas>
                    </div>
                </div>

                <!-- Productivity Mixed Chart -->
                <div class="vuexy-card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300">
                            Volumen de Transacciones & Productividad
                        </h4>
                        <span class="text-[10px] text-gray-400">Año {{ $anio }}</span>
                    </div>
                    <div class="h-[260px]">
                        <canvas id="productivityChart"></canvas>
                    </div>
                </div>

            </section>

            <!-- Dashboard Row 3: Leaderboard (Top Podium + Data Table) -->
            <section class="vuexy-card p-6">
                
                <!-- Title header -->
                <div class="flex justify-between items-center border-b border-gray-100 dark:border-slate-800 pb-4 mb-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <span>🏆</span> Ranking YTD de Sucursales
                        </h3>
                        <p class="text-[10px] text-gray-400 mt-0.5">Clasificación acumulada ponderada de las sucursales.</p>
                    </div>
                    <span class="text-xs bg-brand-primaryLight dark:bg-red-950/20 text-brand-primary font-bold px-3 py-1 rounded-full border border-brand-primary/10">
                        {{ $mesNombre }} {{ $anio }}
                    </span>
                </div>

                <!-- 3D Podium Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 items-end gap-6 mb-12 max-w-3xl mx-auto pt-6">
                    
                    <!-- 2nd Place -->
                    @if(isset($podium[1]))
                    <div class="order-2 md:order-1 flex flex-col items-center">
                        <div class="text-center mb-2">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">2do Lugar</span>
                        </div>
                        <div class="w-full bg-slate-50 dark:bg-slate-800/40 rounded-xl p-5 border border-gray-100 dark:border-slate-700 flex flex-col items-center text-center shadow-sm relative pt-10 min-h-[160px]">
                            <div class="absolute -top-6 w-12 h-12 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full flex items-center justify-center font-bold text-xl border-4 border-[#f8f7fa] dark:border-[#161d31] shadow-md">
                                🥈
                            </div>
                            <span class="text-[9px] px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-full font-mono uppercase font-bold border border-gray-200 dark:border-slate-700">{{ $podium[1]['codigo'] }}</span>
                            <h5 class="text-xs font-bold text-slate-800 dark:text-white mt-3 line-clamp-1 max-w-[170px]">{{ str_replace('KFC - ', '', $podium[1]['cdc']) }}</h5>
                            <div class="mt-4 text-2xl font-black text-slate-700 dark:text-slate-300 font-mono">
                                {{ $podium[1]['score'] }} <span class="text-xs font-normal text-gray-500">pts</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 1st Place -->
                    @if(isset($podium[0]))
                    <div class="order-1 md:order-2 flex flex-col items-center">
                        <div class="text-center mb-2">
                            <span class="text-[10px] font-bold text-yellow-500 uppercase tracking-widest">1er Lugar</span>
                        </div>
                        <div class="w-full bg-gradient-to-b from-[#dc2626] to-[#b91c1c] text-white rounded-2xl p-6 flex flex-col items-center text-center shadow-lg relative pt-12 min-h-[200px] border-2 border-yellow-400 shadow-yellow-500/10">
                            <div class="absolute -top-8 w-16 h-16 bg-yellow-400 text-yellow-950 rounded-full flex items-center justify-center font-bold text-3xl border-4 border-[#f8f7fa] dark:border-[#161d31] shadow-lg">
                                👑
                            </div>
                            <span class="text-[9px] px-2 py-0.5 bg-red-700/80 text-white rounded-full font-mono uppercase font-bold border border-red-500/30">{{ $podium[0]['codigo'] }}</span>
                            <h5 class="text-sm font-bold text-white mt-3 line-clamp-1 max-w-[190px]">{{ str_replace('KFC - ', '', $podium[0]['cdc']) }}</h5>
                            <div class="mt-5 text-3xl font-extrabold font-mono text-yellow-300">
                                {{ $podium[0]['score'] }} <span class="text-xs font-normal text-white/90">pts</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 3rd Place -->
                    @if(isset($podium[2]))
                    <div class="order-3 md:order-3 flex flex-col items-center">
                        <div class="text-center mb-2">
                            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-widest">3er Lugar</span>
                        </div>
                        <div class="w-full bg-slate-50 dark:bg-slate-800/40 rounded-xl p-5 border border-gray-100 dark:border-slate-700 flex flex-col items-center text-center shadow-sm relative pt-10 min-h-[145px]">
                            <div class="absolute -top-6 w-12 h-12 bg-amber-700 text-amber-50 rounded-full flex items-center justify-center font-bold text-xl border-4 border-[#f8f7fa] dark:border-[#161d31] shadow-md">
                                🥉
                            </div>
                            <span class="text-[9px] px-2 py-0.5 bg-slate-800 text-gray-400 rounded-full font-mono uppercase font-bold border border-white/5">{{ $podium[2]['codigo'] }}</span>
                            <h5 class="text-xs font-bold text-slate-800 dark:text-white mt-3 line-clamp-1 max-w-[170px]">{{ str_replace('KFC - ', '', $podium[2]['cdc']) }}</h5>
                            <div class="mt-4 text-2xl font-black text-amber-800 dark:text-amber-500 font-mono">
                                {{ $podium[2]['score'] }} <span class="text-xs font-normal text-gray-500">pts</span>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>

                <!-- Table Leaderboard List -->
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-slate-700 tracking-wider">
                                <th class="px-5 py-3 text-center w-16">Puesto</th>
                                <th class="px-6 py-3">Sucursal (CDC)</th>
                                <th class="px-4 py-3 text-center">Tipo</th>
                                <th class="px-4 py-3 text-center">Dotación</th>
                                <th class="px-4 py-3 text-center">Salidas YTD</th>
                                <th class="px-4 py-3 text-center">Rotación YTD</th>
                                <th class="px-4 py-3 text-center">Retención YTD</th>
                                <th class="px-6 py-3 text-right font-bold text-brand-primary w-24">Score YTD</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-600 dark:text-gray-300">
                            @foreach(array_merge($podium, $restLeaderboard) as $index => $tItem)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors duration-150 {{ $tiendaId == $tItem['id'] ? 'bg-red-500/5 dark:bg-red-950/10 font-semibold text-slate-800 dark:text-white border-l-4 border-l-brand-primary' : '' }}">
                                    <td class="px-5 py-2.5 text-center font-bold">
                                        @if($index == 0)
                                            <span class="inline-flex w-5.5 h-5.5 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-950/50 text-yellow-600 dark:text-yellow-400 font-bold">1</span>
                                        @elseif($index == 1)
                                            <span class="inline-flex w-5.5 h-5.5 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-400 font-bold">2</span>
                                        @elseif($index == 2)
                                            <span class="inline-flex w-5.5 h-5.5 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-500 font-bold">3</span>
                                        @else
                                            <span class="text-gray-400 font-mono text-[11px]">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[9px] font-bold font-mono text-gray-500 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded border border-gray-200 dark:border-slate-700">{{ $tItem['codigo'] }}</span>
                                            <span class="font-semibold text-slate-900 dark:text-white">{{ $tItem['cdc'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border border-gray-200 dark:border-slate-700
                                            {{ $tItem['tipo'] === 'FS' ? 'bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400' : '' }}
                                            {{ $tItem['tipo'] === 'FC' ? 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400' : '' }}
                                            {{ $tItem['tipo'] === 'IL' ? 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400' : '' }}
                                        ">
                                            {{ $tItem['tipo'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-mono">{{ $tItem['headcount'] }}</td>
                                    <td class="px-4 py-2.5 text-center font-mono text-gray-400">{{ $tItem['egresos'] }}</td>
                                    <td class="px-4 py-2.5 text-center font-mono">{{ $tItem['rotacion'] }}</td>
                                    <td class="px-4 py-2.5 text-center font-mono">{{ $tItem['retencion'] }}</td>
                                    <td class="px-6 py-2.5 text-right font-bold font-mono text-slate-900 dark:text-white">
                                        {{ $tItem['score'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </section>

        </div>
    </div>

    <!-- Excel Upload Modal (Vuexy Style) -->
    <div id="uploader-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white dark:bg-brand-vuexyDarkCard border border-gray-200 dark:border-slate-700 rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl relative">
            <button onclick="document.getElementById('uploader-modal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg">✕</button>
            <div class="brand-stripes"></div>
            <div class="p-6 md:p-8">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
                    📂 Cargar Archivo de Datos (Excel)
                </h3>
                <p class="text-xs text-gray-400 leading-relaxed mb-6">
                    Sube el archivo Excel original (`DATA PARA RAY.xlsx`) para reconstruir la base de datos de transacciones, tiendas y personal.
                </p>

                <form action="{{ route('import.excel') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div class="border-2 border-dashed border-gray-200 dark:border-slate-700 hover:border-brand-primary/50 dark:hover:border-brand-primary/50 rounded-xl p-8 text-center cursor-pointer transition-all relative">
                        <input type="file" name="excel_file" id="excel_file_input" required class="absolute inset-0 opacity-0 cursor-pointer" onchange="document.getElementById('file-chosen').textContent = this.files[0].name">
                        <span class="text-4xl block mb-2">📊</span>
                        <span id="file-chosen" class="text-xs font-semibold text-slate-500 dark:text-gray-400 block">Haga clic o arrastre el archivo .xlsx aquí</span>
                    </div>

                    <button type="submit" class="w-full bg-brand-primary hover:bg-red-700 text-white font-bold uppercase tracking-wider text-xs py-3.5 rounded-xl transition-all shadow-lg shadow-red-900/30">
                        🚀 Iniciar Importación
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Render Charts using Chart.js -->
    <script>
        // Set Chart.js Defaults (Vuexy Clean Light/Dark Theme colors)
        const isDark = document.documentElement.classList.contains('dark');
        Chart.defaults.color = isDark ? '#a5a8ad' : '#5d596c';
        Chart.defaults.borderColor = isDark ? '#404656' : '#dbdade';
        Chart.defaults.font.family = 'Public Sans';

        // 1. Rotation trend chart
        const rotCtx = document.getElementById('rotationChart').getContext('2d');
        
        // Gradient fill for rotation
        const rotGradient = rotCtx.createLinearGradient(0, 0, 0, 240);
        rotGradient.addColorStop(0, 'rgba(220, 38, 38, 0.25)');
        rotGradient.addColorStop(1, 'rgba(220, 38, 38, 0.0)');

        const rotationChart = new Chart(rotCtx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [
                    @if($tiendaSeleccionada)
                    {
                        label: 'Rotación Local (%)',
                        data: @json($chartRotationLocal),
                        borderColor: '#dc2626',
                        backgroundColor: rotGradient,
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true
                    },
                    @endif
                    {
                        label: 'Promedio Cadena (%)',
                        data: @json($chartRotationChain),
                        borderColor: '#82868b',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 15, font: { size: 10 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Porcentaje (%)', font: { size: 10 } }
                    }
                }
            }
        });

        // 2. Productivity Mixed Chart (Vuexy Styled Blue/Red)
        const prodCtx = document.getElementById('productivityChart').getContext('2d');
        const productivityChart = new Chart(prodCtx, {
            type: 'bar',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [
                    {
                        label: 'Volumen Transacciones',
                        data: @json($chartTransactions),
                        type: 'bar',
                        backgroundColor: 'rgba(220, 38, 38, 0.75)',
                        hoverBackgroundColor: '#dc2626',
                        borderRadius: 4,
                        barThickness: 15,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Productividad (Trans / Empleado)',
                        data: @json($chartProductivity),
                        type: 'line',
                        borderColor: '#00cfe8', // cyan
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        tension: 0.25,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 15, font: { size: 10 } }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Transacciones (Cant.)', font: { size: 10 } }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Trans. por Colaborador', font: { size: 10 } }
                    }
                }
            }
        });
    </script>
</body>
</html>
