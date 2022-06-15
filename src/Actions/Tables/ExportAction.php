<?php

namespace pxlrbt\FilamentExcel\Actions\Tables;

use Closure;
use Filament\Tables\Actions\Action;
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
        $this->button();
        $this->action(Closure::fromCallable([$this, 'handleExport']));

        $this->exports = collect([
            ExcelExport::make()
                ->fromTable()
                ->queue(),
        ]);
    }

    public function handleExport(array $data)
    {
        $exportable = $this->getSelectedExport($data);

        return app()->call([$exportable, 'hydrate'], [
            'livewire' => $this->getLivewire(),
            'formData' => data_get($data, $exportable->getName()),
        ])->export();
    }
}
