<?php

namespace pxlrbt\FilamentExcel\Actions;

use Closure;
use Filament\Pages\Actions\Action;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;
use pxlrbt\FilamentExcel\Export\BulkExport;
use pxlrbt\FilamentExcel\Export\Export;

class ExportPageAction extends Action
{
    use ExportableAction;

    public static function make(string $name): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        $this->modalWidth = 'sm';
        $this->action(Closure::fromCallable([$this, 'handleExport']));

        $this->exports = collect([
            Export::make()->fromForm(),
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
