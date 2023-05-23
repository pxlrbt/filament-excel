<?php

namespace pxlrbt\FilamentExcel\Actions\Pages;

use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ExportAction extends Action
{
    use ExportableAction {
        ExportableAction::setUp as parentSetUp;
    }

    public static function make(?string $name = 'export'): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        $this->parentSetUp();

        $this->button();
        $this->icon('heroicon-o-download');

        $this->exports = collect([
            ExcelExport::make()->fromForm(),
        ]);
    }

    public function handleExport(array $data)
    {
        $exportable = $this->getSelectedExport($data);
        $livewire = $this->getLivewire();

        return app()->call([$exportable, 'hydrate'], [
            'livewire' => $this->getLivewire(),
            'records' => property_exists($livewire, 'record') ? collect([$livewire->record]) : null,
            'formData' => data_get($data, $exportable->getName()),
        ])->export();
    }
}
