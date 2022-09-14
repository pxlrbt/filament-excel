<?php

namespace pxlrbt\FilamentExcel\Actions\Pages;

use Filament\Pages\Actions\Action;
use pxlrbt\FilamentExcel\Actions\Concerns\ExportableAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ExportAction extends Action
{
    use ExportableAction {
        ExportableAction::setUp as parentSetUp;
        ExportableAction::handleExport as parentHandleExport;
    }

    public static function getDefaultName(): ?string
    {
        return 'export';
    }

    protected function setUp(): void
    {
        $this->parentSetUp();

        $this
            ->button()
            ->exports([ExcelExport::make()->fromForm()]);
    }

    public function handleExport(array $data)
    {
        $record = collect([$this->getLivewire()->record]);

        return $this->parentHandleExport($data, $record);
    }
}
