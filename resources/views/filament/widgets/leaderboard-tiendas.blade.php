<x-filament-widgets::widget>
    <div class="fi-wi-widget p-6 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm transition-all duration-300 hover:shadow-md">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 border-b border-gray-100 dark:border-gray-800 pb-5">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white flex items-center gap-2">
                    <span class="p-2 bg-red-50 dark:bg-red-950 text-red-600 dark:text-red-400 rounded-lg">🏆</span>
                    Ranking de Sucursales: Total Gente
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Clasificación general acumulada YTD para el periodo de <strong>{{ ucfirst($mesNombre) }} {{ $anio }}</strong>.
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 px-3 p-1.5 rounded-full border border-gray-100 dark:border-gray-800">
                <span class="inline-block w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                Calculado en tiempo real
            </div>
        </div>

        <!-- 3D Podium View (Top 3) -->
        <div class="grid grid-cols-1 md:grid-cols-3 items-end gap-6 mb-10 max-w-4xl mx-auto pt-6 px-4">
            
            <!-- 2nd Place: Silver (Column order: 2nd on Left) -->
            @if(isset($podium[1]))
            <div class="order-2 md:order-1 flex flex-col items-center">
                <div class="text-center mb-2">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">2do Lugar</span>
                </div>
                <div class="w-full bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-800/30 dark:to-gray-800/80 rounded-xl p-5 border border-gray-200 dark:border-gray-800 flex flex-col items-center text-center shadow-sm relative pt-10 min-h-[180px]">
                    <div class="absolute -top-6 w-12 h-12 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full flex items-center justify-center font-bold text-xl border-4 border-white dark:border-gray-900 shadow-md">
                        🥈
                    </div>
                    <span class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-full border border-gray-200 dark:border-gray-700 font-mono text-[10px] uppercase font-bold">{{ $podium[1]['codigo'] }}</span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mt-3 line-clamp-1 max-w-[180px]">
                        {{ str_replace('KFC - ', '', $podium[1]['cdc']) }}
                    </h3>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Formato: {{ $podium[1]['formato'] }}</p>
                    <div class="mt-4 text-2xl font-black text-gray-700 dark:text-gray-300 font-mono">
                        {{ $podium[1]['score'] }} <span class="text-xs font-normal text-gray-500">pts</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- 1st Place: Gold (Column order: 1st in Center) -->
            @if(isset($podium[0]))
            <div class="order-1 md:order-2 flex flex-col items-center">
                <div class="text-center mb-2">
                    <span class="text-xs font-bold text-yellow-600 dark:text-yellow-400 uppercase tracking-widest flex items-center gap-1">
                        ⭐ 1er Lugar ⭐
                    </span>
                </div>
                <div class="w-full bg-gradient-to-b from-red-500 to-red-600 text-white rounded-2xl p-6 flex flex-col items-center text-center shadow-xl relative pt-12 min-h-[220px] border-4 border-yellow-400 dark:border-yellow-500">
                    <div class="absolute -top-8 w-16 h-16 bg-yellow-400 text-yellow-950 rounded-full flex items-center justify-center font-bold text-3xl border-4 border-white dark:border-yellow-400 shadow-lg animate-bounce">
                        👑
                    </div>
                    <span class="text-xs px-2 py-0.5 bg-red-600/80 text-white rounded-full font-mono text-[10px] uppercase font-bold border border-red-400/30">{{ $podium[0]['codigo'] }}</span>
                    <h3 class="text-base font-black tracking-tight mt-3 line-clamp-1 max-w-[200px]">
                        {{ str_replace('KFC - ', '', $podium[0]['cdc']) }}
                    </h3>
                    <p class="text-xs text-red-100 mt-1 opacity-90">Formato: {{ $podium[0]['formato'] }}</p>
                    <div class="mt-5 text-4xl font-extrabold font-mono tracking-tight text-yellow-300">
                        {{ $podium[0]['score'] }} <span class="text-sm font-semibold text-white/95">pts</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- 3rd Place: Bronze (Column order: 3rd on Right) -->
            @if(isset($podium[2]))
            <div class="order-3 md:order-3 flex flex-col items-center">
                <div class="text-center mb-2">
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-500 uppercase tracking-widest">3er Lugar</span>
                </div>
                <div class="w-full bg-gradient-to-b from-amber-50/50 to-amber-100/30 dark:from-amber-950/10 dark:to-amber-950/20 rounded-xl p-5 border border-amber-200/50 dark:border-amber-800/30 flex flex-col items-center text-center shadow-sm relative pt-10 min-h-[160px]">
                    <div class="absolute -top-6 w-12 h-12 bg-amber-600 text-amber-50 rounded-full flex items-center justify-center font-bold text-xl border-4 border-white dark:border-gray-900 shadow-md">
                        🥉
                    </div>
                    <span class="text-xs px-2 py-0.5 bg-amber-50 dark:bg-amber-950 text-amber-800 dark:text-amber-400 rounded-full border border-amber-200/40 dark:border-amber-800/40 font-mono text-[10px] uppercase font-bold">{{ $podium[2]['codigo'] }}</span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mt-3 line-clamp-1 max-w-[180px]">
                        {{ str_replace('KFC - ', '', $podium[2]['cdc']) }}
                    </h3>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Formato: {{ $podium[2]['formato'] }}</p>
                    <div class="mt-4 text-2xl font-black text-amber-800 dark:text-amber-400 font-mono">
                        {{ $podium[2]['score'] }} <span class="text-xs font-normal text-gray-500">pts</span>
                    </div>
                </div>
            </div>
            @endif
            
        </div>

        <!-- Leaderboard Table -->
        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase border-b border-gray-100 dark:border-gray-800 tracking-wider">
                        <th class="px-5 py-3.5 text-center w-16">Puesto</th>
                        <th class="px-6 py-3.5">Sucursal (CDC)</th>
                        <th class="px-4 py-3.5 text-center">Tipo</th>
                        <th class="px-4 py-3.5 text-center">Dotación</th>
                        <th class="px-4 py-3.5 text-center">Salidas YTD</th>
                        <th class="px-4 py-3.5 text-center">Rotación YTD</th>
                        <th class="px-4 py-3.5 text-center">Retención YTD</th>
                        <th class="px-6 py-3.5 text-right font-bold text-red-600 dark:text-red-400 w-24">Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm text-gray-700 dark:text-gray-300">
                    <!-- Podium items inside the table for completeness -->
                    @foreach(array_merge($podium, $leaderboard) as $index => $tienda)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors duration-150 {{ $index < 3 ? 'bg-amber-50/10 dark:bg-amber-950/5' : '' }}">
                            <td class="px-5 py-3 text-center font-bold">
                                @if($index == 0)
                                    <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-950 text-yellow-600 dark:text-yellow-400 text-xs">1</span>
                                @elseif($index == 1)
                                    <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs">2</span>
                                @elseif($index == 2)
                                    <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-500 text-xs">3</span>
                                @else
                                    <span class="text-gray-500 font-mono text-xs">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold font-mono text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-700">{{ $tienda['codigo'] }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $tienda['cdc'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border 
                                    {{ $tienda['formato'] === 'FS' ? 'bg-green-50 dark:bg-green-950/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/30' : '' }}
                                    {{ $tienda['formato'] === 'FC' ? 'bg-yellow-50 dark:bg-yellow-950/20 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800/30' : '' }}
                                    {{ $tienda['formato'] === 'IL' ? 'bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800/30' : '' }}
                                ">
                                    {{ $tienda['formato'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-xs">{{ $tienda['headcount'] }}</td>
                            <td class="px-4 py-3 text-center font-mono text-xs text-gray-500">{{ $tienda['egresos'] }}</td>
                            <td class="px-4 py-3 text-center font-mono text-xs">{{ $tienda['rotacion'] }}</td>
                            <td class="px-4 py-3 text-center font-mono text-xs">{{ $tienda['retencion'] }}</td>
                            <td class="px-6 py-3 text-right font-black font-mono text-gray-900 dark:text-white {{ $index < 3 ? 'text-red-600 dark:text-red-400' : '' }}">
                                {{ $tienda['score'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</x-filament-widgets::widget>
