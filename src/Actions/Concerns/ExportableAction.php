<?php

namespace pxlrbt\FilamentExcel\Actions\Concerns;

use Closure;
use Exception;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

trait ExportableAction
{
    protected Collection $exports;

    protected array $formData = [];

    protected function setUp(): void
    {
        $this
            ->modalWidth('md')
            ->label(__('filament-excel::actions.label'))
            ->icon('heroicon-o-download')
            ->action(Closure::fromCallable([$this, 'handleExport']))
            ->exports([ExcelExport::make()->fromTable()]);
    }

    public function getFormSchema(): array
    {
        if ($this->exports->count() > 1 || $this->getExportFormSchemas()->count() > 0) {
            return [
                ...$this->getSelectExportField(),
                ...$this->getExportFormSchemas(),
            ];
        }

        return [];
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
                $schema = $export->container($this->getLivewire())->getFormSchema();

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

    public function handleExport(array $data, ?Collection $records = null)
    {
        $exportable = $this->getSelectedExport($data);

        return app()->call([$exportable, 'hydrate'], [
            'livewire' => $this->getLivewire(),
            'formData' => Arr::get($data, $exportable->getName(), []),
            'records' => $records,
        ])->export();
    }
}
