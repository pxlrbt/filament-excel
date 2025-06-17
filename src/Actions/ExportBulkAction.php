<?php

namespace pxlrbt\FilamentExcel\Actions;

use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;

class ExportBulkAction extends BulkAction
{
    use ExportableAction {
        ExportableAction::setUp as parentSetUp;
    }

    public static function getDefaultName(): ?string
    {
        return 'export';
    }

    protected function setUp(): void
    {
        parent::setUp();

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
