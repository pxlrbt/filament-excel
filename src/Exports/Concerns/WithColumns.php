<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use pxlrbt\FilamentExcel\Columns\Column;

use function Livewire\invade;

trait WithColumns
{
    public Closure|array $columns = [];

    public Closure|array $generatedColumns = [];

    protected ?Collection $cachedMap = null;

    protected ?string $columnsSource = null;

    public function withColumns(Closure|array|string $columns = null): static
    {
        if (is_callable($columns)) {
            $this->columns = $columns;

            return $this;
        }

        $columns = is_array($columns) ? $columns : func_get_args();

        $this->columns = $columns;

        return $this;
    }

    public function getColumns(): array
    {
        $generatedColumns = $this->evaluate($this->generatedColumns);
        $withColumns = $this->evaluate($this->columns);

        if ($this->columnsSource === 'table' && count($withColumns) > 0) {
            $generatedColumnsKeys = array_keys($generatedColumns);
            $withColumnsKeys = array_map(fn ($column) => $column->getName(), $withColumns);

            $columnDiffKeys = array_diff($generatedColumnsKeys, $withColumnsKeys);

            $columns = array_combine($withColumnsKeys, $withColumns);
            foreach ($columnDiffKeys as $key) {
                if (isset($generatedColumns[$key])) {
                    $columns[$key] = $generatedColumns[$key];
                } else if (isset($withColumns[$key])) {
                    $columns[$key] = $withColumns[$key];
                }
            }
        } else {
            $columns = $generatedColumns;

            foreach ($withColumns as $column) {
                $columns[$column->getName()] = $column;
            }
        }

        return $columns;
    }

    public function fromTable(): static
    {
        $this->generatedColumns = fn () => ($this->cachedMap ??= $this->createFieldMappingFromTable())->toArray();

        $this->columnsSource = 'table';

        return $this;
    }

    public function fromForm(): static
    {
        $this->generatedColumns = fn () => ($this->cachedMap ??= $this->createFieldMappingFromForm())->toArray();

        $this->columnsSource = 'form';

        return $this;
    }

    public function fromModel(): static
    {
        $this->generatedColumns = function () {
            $mapping = $this->getResourceClass() !== null
                ? $this->createFieldMappingFromForm()
                : collect();

            return collect($this->getModelClass()::first()->getAttributes())
                ->map(
                    fn ($attribute, $key) => $mapping->has($key)
                    ? Column::make($key)->heading($mapping->get($key)->getHeading())
                    : Column::make($key)
                )
                ->toArray();
        };

        $this->columnsSource = 'model';

        return $this;
    }

    protected function createFieldMappingFromForm(): Collection
    {
        $form = $this->getResourceClass()::form(new Form($this->getLivewire()));
        $components = collect($form->getComponents());
        $extracted = collect();

        while (($component = $components->shift()) !== null) {
            $children = $component->getChildComponents();

            if (
                $component instanceof Repeater
                || $component instanceof Builder
            ) {
                $extracted->push($component);

                continue;
            }

            if (count($children) > 0) {
                $components = $components->merge($children);

                continue;
            }

            $extracted->push($component);
        }

        return $extracted
            ->filter(fn ($field) => $field instanceof Field)
            ->mapWithKeys(fn (Field $field) => [
                $field->getName() => Column::make($field->getName())
                    ->heading($field->getLabel()),
            ]);
    }

    protected function createFieldMappingFromTable(): Collection
    {
        $livewire = $this->getLivewire();

        if ($livewire instanceof HasTable) {
            $columns = collect($livewire->getTable()->getColumns());
        } else {
            $table = $this->getResourceClass()::table(new Table());
            $columns = collect($table->getColumns());
        }

        return $columns
            ->when(
                $livewire->getTable()->hasToggleableColumns(),
                fn ($collection) => $collection->reject(
                    fn (Tables\Columns\Column $column) => $livewire->isTableColumnToggledHidden($column->getName())
                )
            )
            ->mapWithKeys(function (Tables\Columns\Column $column) {
                $clonedCol = clone $column;

                // Invade for protected properties
                $invadedColumn = invade($clonedCol);

                $exportColumn = Column::make($column->getName())
                    ->heading($column->getLabel())
                    ->getStateUsing($invadedColumn->getStateUsing)
                    ->tableColumn($clonedCol);

                rescue(fn () => $exportColumn->formatStateUsing($invadedColumn->formatStateUsing), report: false);

                return [
                    $column->getName() => $exportColumn,
                ];
            });
    }
}
