<?php

namespace pxlrbt\FilamentExcel\Actions\Tables;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;

class ExportBulkAction extends BulkAction
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

        $this->deselectRecordsAfterCompletion();
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
