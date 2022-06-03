<?php

namespace pxlrbt\FilamentExcel\Actions;

use Closure;
use Filament\Tables\Actions\Action;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;
use pxlrbt\FilamentExcel\Export\SingleExport;

class ExportTableAction extends Action
{
    use ExportableAction;

    protected function setUp(): void
    {
        $this->modalWidth = 'sm';
        $this->action(Closure::fromCallable([$this, 'export']));

        $this->exportables = collect([SingleExport::make('export')->fromForm()]);
    }

    public function export(array $data)
    {
        dd($this->getLivewire()->getAllTableRecordKeys());
        dd(\Livewire\invade($this->getLivewire())->getRecords());
        $exportable = $this->getSelectedExportable($data);
        $record = $this->getLivewire()->record;

        return app()->call([$exportable, 'hydrate'], [
            'livewire' => $this->getLivewire(),
            'resource' => new ($this->getLivewire()::getResource()),
            'record' => $record,
            'formData' => data_get($data, $exportable->getName()),
        ])->export();
    }
}
