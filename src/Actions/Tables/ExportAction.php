<?php

namespace pxlrbt\FilamentExcel\Actions\Tables;

use Filament\Tables\Actions\Action;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ExportAction extends Action
{
    use ExportableAction {
        ExportableAction::handleExport as parentHandleExport;
    }

    public static function getDefaultName(): ?string
    {
        return 'export';
    }

    public function handleExport(array $data, $record = null)
    {
        if ($record) {
            $record = collect([$record]);
        }

        return $this->parentHandleExport($data, $record);
    }
}
