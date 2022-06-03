<?php

namespace pxlrbt\FilamentExcel\Actions;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;

class TableExportBulkAction extends BulkAction
{
    use ExportableAction;

    public function export(Collection $records, array $data)
    {
        $exportable = $this->getSelectedExportable($data);

        return app()->call([$exportable, 'hydrate'], [
            'livewire' => $this->getLivewire(),
            'records' => $records,
            'formData' => data_get($data, $exportable->getName()),
        ])->export();
    }
}
