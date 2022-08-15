<?php

namespace pxlrbt\FilamentExcel\Actions\Tables;

use Filament\Tables\Actions\Action;
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

        $this->label(__('filament-excel::actions.label'));
        $this->button();
        $this->icon('heroicon-o-download');

        $this->exports = collect([
            ExcelExport::make()->fromTable(),
        ]);
    }

    public function handleExport(array $data)
    {
        $exportable = $this->getSelectedExport($data);

        return app()->call([$exportable, 'hydrate'], [
            'livewire' => $this->getLivewire(),
            'formData' => data_get($data, $exportable->getName()),
        ])->export();
    }
}
