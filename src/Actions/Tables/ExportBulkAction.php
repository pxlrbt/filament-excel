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

    public static function getDefaultName(): ?string
    {
        return 'export';
    }

    protected function setUp(): void
    {
        $this->parentSetUp();

        $this->deselectRecordsAfterCompletion();
    }
}
