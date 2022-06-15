<?php

namespace pxlrbt\FilamentExcel\Concerns;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Tables\Columns\Column;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use function Livewire\invade;

trait WithFields
{
    public Closure | array $autoFields = [];

    public Closure | array $fields = [];

    protected function mergeNumericArray($array1, $array2)
    {
        $result = $array1;

        foreach ($array2 as $key => $value) {
            if (filled($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function fromTable(): static
    {
        $this->autoFields = function () {
            return $this->getTableMapping()->map(fn ($label, $key) => $key)->toArray();
        };

        $this->autoHeadings = function () {
            return $this->getTableMapping()->map(fn ($label, $key) => $label)->toArray();
        };

        return $this;
    }

    public function fromForm(): static
    {
        $this->autoFields = function () {
            return $this->getFormMapping()->map(fn ($label, $key) => $key)->toArray();
        };

        $this->autoHeadings = function () {
            return $this->getFormMapping()->map(fn ($label, $key) => $label)->toArray();
        };

        return $this;
    }

    public function fromModel(): static
    {
        $this->autoFields = function () {
            return array_keys($this->getModelClass()::first()->getAttributes());
        };

        $this->autoHeadings = function () {
            return array_keys($this->getModelClass()::first()->getAttributes());
        };

        return $this;
    }

    public function withFields(Closure | array | string | null $fields = null): self
    {
        if (is_callable($fields)) {
            $this->fields = $fields;

            return $this;
        }

        $fields = is_array($fields) ? $fields : func_get_args();

        $this->fields = $fields;

        return $this;
    }

    public function getFields(): array
    {
        return $this->mergeNumericArray(
            $this->evaluate($this->autoFields) ?? [],
            $this->evaluate($this->fields) ?? [],
        );
    }

    public function getAutoHeadings(): array
    {
        return $this->evaluate($this->autoHeadings);
    }

    // Field resolution
    protected ?Collection $cachedMap = null;

    public function getTableMapping(): Collection
    {
        return $this->cachedMap ??= $this->createFieldMappingFromTable();
    }

    public function getFormMapping(): Collection
    {
        return $this->cachedMap ??= $this->createFieldMappingFromForm();
    }

    protected function createFieldMappingFromForm(): Collection
    {
        $form = $this->getResource()::form(new Form());
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
            ->mapWithKeys(fn (Field $field) => [$field->getName() => $field->getLabel()]);
    }

    protected function createFieldMappingFromTable(): Collection
    {
        if ($this->getLivewire() instanceof HasTable) {
            $columns = collect(invade($this->getLivewire())->getTableColumns());
        } else {
            $table = $this->getResource()::table(new Table());
            $columns = collect($table->getColumns());
        }

        return $columns->mapWithKeys(fn (Column $column) => [$column->getName() => $column->getLabel()]);
    }
}
