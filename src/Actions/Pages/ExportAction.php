<?php

namespace pxlrbt\FilamentExcel\Actions;

use Closure;
use Filament\Pages\Actions\Action;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;
use pxlrbt\FilamentExcel\Export\ExcelExport;

class ExportAction extends Action
{
    use ExportableAction;

    public static function make(string $name = 'export'): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        $this->modalWidth = 'sm';
        $this->action(Closure::fromCallable([$this, 'handleExport']));

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
