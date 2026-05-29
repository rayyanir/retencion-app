<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use App\Models\Tienda;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function mount()
    {
        return redirect('/');
    }

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('anio')
                            ->label('Año')
                            ->options([
                                2023 => '2023',
                                2024 => '2024',
                                2025 => '2025',
                                2026 => '2026',
                            ])
                            ->default(2026)
                            ->selectablePlaceholder(false),
                        Select::make('mes')
                            ->label('Mes')
                            ->options([
                                1 => 'Enero',
                                2 => 'Febrero',
                                3 => 'Marzo',
                                4 => 'Abril',
                                5 => 'Mayo',
                                6 => 'Junio',
                                7 => 'Julio',
                                8 => 'Agosto',
                                9 => 'Septiembre',
                                10 => 'Octubre',
                                11 => 'Noviembre',
                                12 => 'Diciembre',
                            ])
                            ->default(4)
                            ->selectablePlaceholder(false),
                        Select::make('tienda_id')
                            ->label('Sucursal (Filtro Local)')
                            ->options(
                                Tienda::where('codigo_corto', '!=', 'KGEN')
                                    ->orderBy('cdc')
                                    ->pluck('cdc', 'id')
                                    ->toArray()
                            )
                            ->placeholder('Todas las Sucursales (Promedio Cadena)')
                            ->searchable(),
                    ])
                    ->columns(3),
            ]);
    }
}
