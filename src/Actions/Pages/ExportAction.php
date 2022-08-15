<?php

namespace pxlrbt\FilamentExcel\Actions\Pages;

use Filament\Pages\Actions\Action;
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
        $record = $this->getLivewire()->record;

        return app()->call([$exportable, 'hydrate'], [
            'livewire' => $this->getLivewire(),
            'records' => collect([$record]),
            'formData' => data_get($data, $exportable->getName()),
        ])->export();
    }
}
