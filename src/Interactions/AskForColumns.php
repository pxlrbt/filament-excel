<?php

namespace pxlrbt\FilamentExcel\Interactions;

use Filament\Forms\Components\Builder;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Forms\Components\ExportBuilder;

trait AskForColumns
{
    public function askForColumns(?array $default = null, ?string $label = null, ?callable $callback = null): self
    {
        $this->formSchema[] = fn ($query) => self::getColumnsField($this->getColumns(), $default, $label, $callback);

        return $this;
    }

    public static function getColumnsField(array $availableColumns = [], ?array $default = null, ?string $label = null, ?callable $callback = null): Builder
    {
        $columns = [];
        foreach ($availableColumns as $column) {
            /** @var Column $column */
            $columns[] = Builder\Block::make($column->getName())
                ->label($column->getLabel())
                ->schema($column->getFormSchema());
        }

        $field = ExportBuilder::make('columns')
            ->label($label ?? __('filament-excel::fields.columns.label'))
            ->hint(__('filament-excel::fields.columns.hint'))
            ->default($default ?? [])
            ->required()
            ->blocks($columns);

        if (is_callable($callback)) {
            $callback($field);
        }

        return $field;
    }
}
