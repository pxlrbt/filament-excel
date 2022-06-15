<?php

namespace pxlrbt\FilamentExcel\Actions;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;

class ExportBulkAction extends BulkAction
{
    use ExportableAction;

    public static function make(string $name): static
    {
        return parent::make($name);
    }

    public function handleExport(Collection $records, array $data)
    {
        $exportable = $this->getSelectedExport($data);

        return app()->call([$exportable, 'hydrate'], [
            'livewire' => $this->getLivewire(),
            'records' => $records,
            'formData' => data_get($data, $exportable->getName()),
        ])->export();
    }
}
