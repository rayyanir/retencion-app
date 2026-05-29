<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContratoResource\Pages;
use App\Models\Contrato;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContratoResource extends Resource
{
    protected static ?string $model = Contrato::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Historial Laboral';
    protected static ?string $navigationGroup = 'Gestión de Personal';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('empleado_id')
                    ->relationship('empleado', 'nombre')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Colaborador'),
                Forms\Components\Select::make('tienda_id')
                    ->relationship('tienda', 'cdc')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Sucursal (CDC)'),
                Forms\Components\DatePicker::make('fecha_ingreso')
                    ->required()
                    ->label('Fecha de Ingreso'),
                Forms\Components\DatePicker::make('fecha_egreso')
                    ->label('Fecha de Egreso'),
                Forms\Components\TextInput::make('banda')
                    ->maxLength(100)
                    ->placeholder('Ej: Sindicalizado')
                    ->label('Banda'),
                Forms\Components\TextInput::make('puesto')
                    ->maxLength(100)
                    ->placeholder('Ej: ASOCIADO')
                    ->label('Puesto / Cargo'),
                Forms\Components\TextInput::make('turno')
                    ->maxLength(100)
                    ->placeholder('Ej: DIURNO')
                    ->label('Turno'),
                Forms\Components\Select::make('status')
                    ->options([
                        'ACTIVO' => 'ACTIVO',
                        'EGRESO' => 'EGRESO',
                    ])
                    ->required()
                    ->label('Estado'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('empleado.cedula')
                    ->searchable()
                    ->sortable()
                    ->label('Cédula'),
                Tables\Columns\TextColumn::make('empleado.nombre')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre Completo'),
                Tables\Columns\TextColumn::make('tienda.codigo_corto')
                    ->searchable()
                    ->sortable()
                    ->label('Tienda'),
                Tables\Columns\TextColumn::make('fecha_ingreso')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Ingreso'),
                Tables\Columns\TextColumn::make('fecha_egreso')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Egreso')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('puesto')
                    ->searchable()
                    ->sortable()
                    ->label('Cargo'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ACTIVO' => 'success',
                        'EGRESO' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->label('Estado'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'ACTIVO' => 'ACTIVO',
                        'EGRESO' => 'EGRESO',
                    ])
                    ->label('Estado'),
                Tables\Filters\SelectFilter::make('tienda_id')
                    ->relationship('tienda', 'cdc')
                    ->searchable()
                    ->label('Sucursal (CDC)'),
                Tables\Filters\Filter::make('fecha_ingreso')
                    ->form([
                        Forms\Components\DatePicker::make('ingreso_desde')->label('Ingreso Desde'),
                        Forms\Components\DatePicker::make('ingreso_hasta')->label('Ingreso Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['ingreso_desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_ingreso', '>=', $date),
                            )
                            ->when(
                                $data['ingreso_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_ingreso', '<=', $date),
                            );
                    })
                    ->label('Fecha de Ingreso'),
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
            'index' => Pages\ListContratos::route('/'),
            'create' => Pages\CreateContrato::route('/create'),
            'edit' => Pages\EditContrato::route('/{record}/edit'),
        ];
    }
}
