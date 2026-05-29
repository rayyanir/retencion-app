<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ImportarDatos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationLabel = 'Importar Excel';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.importar-datos';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('excel_file')
                    ->label('Seleccionar Libro Excel (DATA PARA RAY.xlsx)')
                    ->helperText('Sube el archivo Excel que contiene las pestañas de Productividad y Personal.')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                        'application/vnd.ms-excel'
                    ])
                    ->disk('local')
                    ->directory('imports')
                    ->visibility('private')
                    ->preserveFilenames(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('importar')
                ->label('Iniciar Ingestión de Datos')
                ->color('danger')
                ->icon('heroicon-m-play')
                ->submit('importar'),
        ];
    }

    public function importar(): void
    {
        $inputData = $this->form->getState();
        $fileName = $inputData['excel_file'];
        
        $path = Storage::disk('local')->path($fileName);

        if (!file_exists($path)) {
            Notification::make()
                ->title('Error')
                ->body('No se pudo encontrar el archivo subido en el servidor.')
                ->danger()
                ->send();
            return;
        }

        try {
            // Call the Artisan command directly and pass the custom file path
            $exitCode = Artisan::call('kfc:import', [
                '--file' => $path,
            ]);

            if ($exitCode === 0) {
                Notification::make()
                    ->title('Éxito')
                    ->body('Los datos se han procesado e ingresado correctamente a la base de datos.')
                    ->success()
                    ->persistent()
                    ->send();
            } else {
                $output = Artisan::output();
                Notification::make()
                    ->title('Error en la importación')
                    ->body("Detalle: " . substr($output, 0, 200))
                    ->danger()
                    ->persistent()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Excepción durante la carga')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
