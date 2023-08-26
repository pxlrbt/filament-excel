<?php

namespace pxlrbt\FilamentExcel\Actions\Concerns;

use Closure;
use Exception;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Illuminate\Support\Collection;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

trait ExportableAction
{
    protected Collection $exports;

    protected array $formData = [];

    protected function setUp(): void
    {
        $this->modalWidth('md');

        $this->label(__('filament-excel::actions.label'));
        $this->icon('heroicon-o-arrow-down-tray');
        $this->action(Closure::fromCallable([$this, 'handleExport']));

        $this->form(function () {
            if ($this->exports->count() > 1 || $this->getExportFormSchemas()->count() > 0) {
                return [
                    ...$this->getSelectExportField(),
                    ...$this->getExportFormSchemas(),
                ];
            }

            return [];
        });

        $this->exports = collect([ExcelExport::make('export')->fromTable()]);
    }

    protected function getSelectExportField(): array
    {
        return [
            Select::make('selected_exportable')
                ->label(__('Export template'))
                ->reactive()
                ->default(0)
                ->disablePlaceholderSelection()
                ->hidden($this->exports->count() <= 1)
                ->options($this->exports->map(
                    fn ($export) => $export->getLabel()
                )),
        ];
    }

    protected function getExportFormSchemas(): Collection
    {
        return $this->exports
            ->map(function (ExcelExport $export, $key) {
                $schema = $export->getFormSchema();

                return empty($schema)
                    ? null
                    : Group::make($schema)
                        ->statePath($export->getName())
                        ->visible(fn ($get) => filled($get('selected_exportable')) && $get('selected_exportable') == $key);
            })
            ->filter();
    }

    protected function getSelectedExport($data): ExcelExport
    {
        if ($this->exports->isEmpty()) {
            throw new Exception('No export templates defined');
        }

        return $this->exports->get(
            data_get($data, 'selected_exportable', 0)
        );
    }

    public function exports(array $exports): static
    {
        $this->exports = collect($exports);

        return $this;
    }
}
