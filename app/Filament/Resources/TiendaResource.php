<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TiendaResource\Pages;
use App\Models\Tienda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TiendaResource extends Resource
{
    protected static ?string $model = Tienda::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'Sucursales (CDC)';
    protected static ?string $navigationGroup = 'Estructura Organizacional';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo_corto')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Ej: K01')
                    ->label('Código Corto'),
                Forms\Components\TextInput::make('cdc')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Ej: KFC - 001 LOS CORTIJOS')
                    ->label('Centro de Costos (CDC)'),
                Forms\Components\Select::make('tipo')
                    ->options([
                        'FC' => 'Food Court (FC)',
                        'FS' => 'Free Standing (FS)',
                        'IL' => 'In-Line (IL)',
                    ])
                    ->required()
                    ->label('Tipo / Formato'),
                Forms\Components\Select::make('zona_id')
                    ->relationship('zona', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Zona'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo_corto')
                    ->searchable()
                    ->sortable()
                    ->label('Código'),
                Tables\Columns\TextColumn::make('cdc')
                    ->searchable()
                    ->sortable()
                    ->label('Sucursal (CDC)'),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'FC' => 'warning',
                        'FS' => 'success',
                        'IL' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->label('Formato'),
                Tables\Columns\TextColumn::make('zona.nombre')
                    ->sortable()
                    ->label('Zona'),
                Tables\Columns\TextColumn::make('contratos_active_count')
                    ->counts('contratos', 'id', fn ($query) => $query->where('status', 'ACTIVO'))
                    ->label('Personal Activo'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('zona')
                    ->relationship('zona', 'nombre')
                    ->label('Zona'),
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'FC' => 'Food Court',
                        'FS' => 'Free Standing',
                        'IL' => 'In-Line',
                    ])
                    ->label('Formato'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTiendas::route('/'),
            'create' => Pages\CreateTienda::route('/create'),
            'edit' => Pages\EditTienda::route('/{record}/edit'),
        ];
    }
}
