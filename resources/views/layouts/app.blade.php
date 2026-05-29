<!DOCTYPE html>
<html lang="es" class="h-full bg-[#f8f7fa] dark:bg-[#161d31]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KFC Venezuela - Métricas de Personal')</title>
    
    <!-- Fonts & CDNs -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;950&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Init theme dark/light before render
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

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
            transition: background-color 0.3s;
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
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.06);
            border-radius: 12px;
            border: 1px solid rgba(219, 218, 222, 0.5);
            transition: all 0.3s;
        }
        .dark .navbar-floating {
            background: rgba(36, 43, 61, 0.85);
            box-shadow: 0 4px 24px 0 rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(64, 70, 86, 0.5);
        }

        /* Vuexy Card Styling */
        .vuexy-card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 18px 0 rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(219, 218, 222, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dark .vuexy-card {
            background-color: #242b3d;
            border: 1px solid rgba(64, 70, 86, 0.4);
            box-shadow: 0 4px 18px 0 rgba(0, 0, 0, 0.25);
        }
        .vuexy-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px 0 rgba(15, 23, 42, 0.07);
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
    @yield('styles')
</head>
<body class="h-full antialiased overflow-x-hidden">

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden transition-all duration-300"></div>

    <!-- LEFT SIDEBAR -->
    <aside id="sidebar-container" class="sidebar flex flex-col justify-between -translate-x-full lg:translate-x-0 duration-300">
        <div>
            <!-- Brand Header -->
            <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="text-2xl animate-pulse">🍗</span>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xl font-black tracking-tighter text-slate-800 dark:text-white">KFC</span>
                            <span class="text-[9px] uppercase bg-brand-primary text-white font-extrabold px-1 rounded tracking-wider">Metrics</span>
                        </div>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest block leading-none">Venezuela</span>
                    </div>
                </div>
                <!-- Close Mobile Sidebar Button -->
                <button onclick="toggleMobileSidebar()" class="lg:hidden text-gray-400 hover:text-red-500 text-lg leading-none">
                    ✕
                </button>
            </div>

            <!-- Brand Stripes Accent -->
            <div class="brand-stripes"></div>

            <!-- Navigation Links -->
            <nav class="px-4 py-6 space-y-1">
                <a href="/" class="{{ request()->is('/') ? 'menu-link-active' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-semibold transition-all">
                    <span>📊</span>
                    <span>Dashboard / RRHH</span>
                </a>
                
                <a href="/sucursales" class="{{ request()->is('sucursales') ? 'menu-link-active' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-semibold transition-all">
                    <span>🏬</span>
                    <span>Sucursales (CDC)</span>
                </a>
                
                <a href="/colaboradores" class="{{ request()->is('colaboradores') ? 'menu-link-active' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-semibold transition-all">
                    <span>👥</span>
                    <span>Colaboradores</span>
                </a>

                <a href="/contratos" class="{{ request()->is('contratos') ? 'menu-link-active' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-semibold transition-all">
                    <span>📝</span>
                    <span>Historial Contratos</span>
                </a>

                <div class="pt-6 pb-2 px-4 text-[10px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider">
                    Gestión de Datos
                </div>

                <a href="{{ route('datos.upload_form') }}" class="{{ request()->routeIs('datos.upload_form') ? 'menu-link-active' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-semibold transition-all">
                    <span>📂</span>
                    <span>Carga de Archivos</span>
                </a>

                <a href="{{ route('datos.editor') }}" class="{{ request()->routeIs('datos.editor') ? 'menu-link-active' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-semibold transition-all">
                    <span>✏️</span>
                    <span>Editor Manual</span>
                </a>

                <div class="pt-4 pb-2 px-4 text-[10px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider">
                    Clima Laboral
                </div>

                <a href="/engagement" class="{{ request()->is('engagement*') ? 'menu-link-active' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-100 dark:hover:bg-slate-800' }} flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-semibold transition-all">
                    <span>🤝</span>
                    <span>Engagement</span>
                </a>


                <div class="pt-6 pb-2 px-4 text-[10px] uppercase font-bold text-gray-400 dark:text-gray-500 tracking-wider">
                    Administración Laravel
                </div>

                <a href="/admin" class="flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium text-slate-500 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    <span>⚙️</span>
                    <span>Base de Datos Raw</span>
                </a>
            </nav>
        </div>

        <!-- Footer brand -->
        <div class="p-6 border-t border-gray-100 dark:border-slate-800 text-[10px] text-gray-400 font-semibold text-center">
            &copy; {{ date('Y') }} KFC Venezuela v2.0
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="lg:pl-[260px] flex flex-col min-h-screen">
        
        <!-- Floating Navbar -->
        <div class="px-6 pt-5">
            <header class="navbar-floating w-full px-6 py-4 flex items-center justify-between z-20">
                <!-- Left: Burger Menu & Breadcrumbs -->
                <div class="flex items-center gap-3">
                    <button onclick="toggleMobileSidebar()" class="lg:hidden p-2 text-slate-800 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg" title="Abrir Menú">
                        ☰
                    </button>
                    <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        <span class="text-gray-400">KFC Venezuela</span>
                        <span>/</span>
                        <span class="text-slate-800 dark:text-white font-bold">@yield('page_title', 'Métricas')</span>
                    </div>
                </div>

                <!-- Right items -->
                <div class="flex items-center gap-4">
                    <!-- Light/Dark Mode Switcher -->
                    <button onclick="toggleDarkMode()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full text-lg leading-none" title="Cambiar Tema">
                        <span class="block dark:hidden">🌙</span>
                        <span class="hidden dark:block">☀️</span>
                    </button>
                    
                    <!-- User Profile Dropdown -->
                    <div class="flex items-center gap-3 border-l border-gray-200 dark:border-slate-700 pl-4 relative group cursor-pointer py-1">
                        <div class="text-right leading-none hidden sm:block">
                            <span class="text-xs font-bold text-slate-800 dark:text-white block">{{ auth()->user()->name ?? 'Administrador KFC' }}</span>
                            <span class="text-[9px] text-gray-400 uppercase font-semibold">Superusuario</span>
                        </div>
                        <span class="w-8 h-8 rounded-full bg-brand-primary text-white font-bold text-xs flex items-center justify-center border-2 border-white dark:border-slate-900 shadow">
                            {{ substr(auth()->user()->name ?? 'AD', 0, 2) }}
                        </span>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-brand-vuexyDarkCard border border-gray-100 dark:border-slate-800 rounded-xl shadow-xl py-2 hidden group-hover:block hover:block z-50">
                            <div class="px-4 py-2 border-b border-gray-50 dark:border-slate-800 mb-1">
                                <p class="text-xs font-bold text-slate-800 dark:text-white">{{ auth()->user()->name ?? 'Admin KFC' }}</p>
                                <p class="text-[9px] text-gray-400">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="/importar" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                📂 Carga de Datos
                            </a>
                            <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="w-full text-left flex items-center gap-2 px-4 py-2 text-xs text-red-600 dark:text-red-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                🚪 Cerrar Sesión
                            </button>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </header>
        </div>

        <!-- Main Body Content -->
        <main class="px-6 py-6 flex-grow">
            @yield('content')
        </main>
        
    </div>

    <!-- Theme and Responsive sidebar scripts -->
    <script>
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            // Dispatch custom event to let charts re-draw with appropriate font colors
            window.dispatchEvent(new Event('theme-changed'));
        }

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar-container');
            const backdrop = document.getElementById('sidebar-backdrop');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
