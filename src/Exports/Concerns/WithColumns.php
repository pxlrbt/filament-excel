<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;

use function Livewire\invade;

use pxlrbt\FilamentExcel\Columns\Column;

trait WithColumns
{
    public Closure | array $columns = [];

    public Closure | array $generatedColumns = [];

    protected ?Collection $cachedMap = null;

    protected ?string $columnsSource = null;

    public function withColumns(Closure | array | string | null $columns = null): static
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
        $columns = $this->evaluate($this->generatedColumns);

        foreach ($this->evaluate($this->columns) as $column) {
            $columns[$column->getName()] = $column;
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
        $form = $this->getResourceClass()::form(new Form());
        $components = collect($form->getSchema());
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
            $columns = collect(invade($this->getLivewire())->getTableColumns());
        } else {
            $table = $this->getResourceClass()::table(new Table());
            $columns = collect($table->getColumns());
        }

        return $columns
            ->when(
                $livewire->hasToggleableTableColumns(),
                fn ($collection) => $collection->reject(
                    fn (Tables\Columns\Column $column) => $livewire->isTableColumnToggledHidden($column->getName())
                )
            )
            ->mapWithKeys(function (Tables\Columns\Column $column) {
                $clonedCol = clone $column;
                $invadedColumn = invade($clonedCol);

                $exportColumn = Column::make($column->getName())
                    ->heading($column->getLabel())
                    ->getStateUsing($invadedColumn->getStateUsing)
                    ->tableColumn($column);

                rescue(fn () => $exportColumn->formatStateUsing($invadedColumn->formatStateUsing), report: false);

                return [
                    $column->getName() => $exportColumn,
                ];
            });
    }
}
