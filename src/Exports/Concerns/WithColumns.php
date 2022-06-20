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

    public function withColumns(Closure | array | string | null $columns = null): self
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

        return $this;
    }

    public function fromForm(): static
    {
        $this->generatedColumns = fn () => ($this->cachedMap ??= $this->createFieldMappingFromForm())->toArray();

        return $this;
    }

    public function fromModel(): static
    {
        $this->generatedColumns = function () {
            $mapping = $this->createFieldMappingFromForm();

            return collect($this->getModelClass()::first()->getAttributes())
                ->map(fn ($attribute, $key) => $mapping->has($key)
                    ? Column::make($key)->heading($mapping->get($key)->getHeading())
                    : Column::make($key)
                )
                ->toArray();
        };

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
            ->mapWithKeys(
                fn (Field $field) => [$field->getName() => Column::make($field->getName())->heading($field->getLabel())]
            );
    }

    protected function createFieldMappingFromTable(): Collection
    {
        if ($this->getLivewire() instanceof HasTable) {
            $columns = collect(invade($this->getLivewire())->getTableColumns());
        } else {
            $table = $this->getResourceClass()::table(new Table());
            $columns = collect($table->getColumns());
        }

        return $columns->mapWithKeys(
            fn (Tables\Columns\Column $column) => [
                $column->getName() => Column::make($column->getName())->heading($column->getLabel()),
            ]
        );
    }
}
