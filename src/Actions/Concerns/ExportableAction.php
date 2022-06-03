<?php

namespace pxlrbt\FilamentExcel\Actions\Concerns;

use Closure;
use Exception;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Illuminate\Support\Collection;
use pxlrbt\FilamentExcel\Export\Export;
use pxlrbt\FilamentExcel\Export\BulkExport;

trait ExportableAction
{
    protected Collection $exportables;

    protected array $formData = [];

    protected function setUp(): void
    {
        $this->modalWidth = 'sm';
        $this->action(Closure::fromCallable([$this, 'export']));

        $this->exportables = collect([BulkExport::make('export')->fromTable()]);
    }


    public function getFormSchema(): array
    {
        if ($this->exportables->count() > 1 || $this->getExportableFormSchemas()->count() > 0) {
            return [
                ...$this->getSelectExportableField(),
                ...$this->getExportableFormSchemas(),
            ];
        }

        return [];
    }

    protected function getSelectExportableField(): array
    {
        return [
            Select::make('selected_exportable')
                ->label(__('Export template'))
                ->reactive()
                ->default(0)
                ->disablePlaceholderSelection()
                ->hidden($this->exportables->count() <= 1)
                ->options($this->exportables->map(
                    fn ($exportable) => $exportable->getLabel()
                ))
        ];
    }

    protected function getExportableFormSchemas(): Collection
    {
        return $this->exportables
            ->map(function (Export $exportable, $key) {
                $schema = $exportable->getFormSchema();

                return empty($schema)
                    ? null
                    : Group::make($schema)
                        ->statePath($exportable->getName())
                        ->visible(fn($get) => filled($get('selected_exportable')) && $get('selected_exportable') == $key);
            })
            ->filter();
    }

    protected function getSelectedExportable($data): Export
    {
        if ($this->exportables->isEmpty()) {
            throw new Exception('No exportables defined');
        }

        return $this->exportables->get(
            data_get($data, 'selected_exportable', 0)
        );
    }

    public function exportables(array $exportables): static
    {
        $this->exportables = collect($exportables);

        return $this;
    }
}
