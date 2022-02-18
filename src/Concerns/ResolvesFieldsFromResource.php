<?php

namespace pxlrbt\FilamentExcel\Concerns;

use Filament\Forms\Components\Field;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Collection;

trait ResolvesFieldsFromResource
{
    protected ?Collection $cachedMap = null;

    protected function getFieldMapping(): Collection
    {
        return $this->cachedMap ??= match ($this->fieldSource) {
            'table' => $this->createFieldMappingFromTable(),
            default => $this->createFieldMappingFromForm(),
        };
    }

    protected function createFieldMappingFromForm(): Collection
    {
        $form = $this->resource::form(new Form());
        $components = collect($form->getSchema());
        $extracted = collect();

        while (($component = $components->shift()) !== null) {
            $children = $component->getChildComponents();

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
        $table = $this->resource::table(new Table());
        $components = collect($table->getColumns());

        return $components->mapWithKeys(fn (Column $column) => [$column->getName() => $column->getLabel()]);
    }
}
