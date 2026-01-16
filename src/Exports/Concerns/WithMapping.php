<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\Formatters\Formatter;

trait WithMapping
{
    public function getMapping($row): Collection
    {
        $columns = collect($this->getColumns());

        $only = $this->getOnly();
        $except = $this->getExcept();

        if ($row instanceof Model) {
            // If user didn't specify a custom except array, use the hidden columns.
            // User can override this by passing an empty array ->except([])
            // When user specifies with only(), ignore if the column is hidden or not.
            if ($except === null && (! is_array($only) || count($only) === 0)) {
                $except = $row->getHidden();
            }
        }

        if (is_array($only) && count($only) > 0) {
            $columns = $columns->only($only);
        }

        if (is_array($except) && count($except) > 0) {
            $columns = $columns->except($except);
        }

        return $columns;
    }

    /**
     * @param  Model|mixed  $record
     */
    public function map($record): array
    {
        $result = [];
        $columns = $this->getMapping($record);

        if ($record instanceof Model) {
            $record->setHidden([]);
        }

        foreach ($columns as $column) {
            $state = $this->getState($column, $record);
            $state = $this->applyFormatStateUsing($column, $record, $state);

            $result[$column->getName()] = app(Formatter::class)->format($state);
        }

        return $result;
    }

    private function getState(Column $column, $record)
    {
        $key = $column->getName();

        if ($this->columnsSource === 'table' && $column->tableColumn !== null) {
            $column->tableColumn->record($record);
            $state = $column->tableColumn->getStateFromRecord();
        } else {
            $state = data_get($record, $key);
        }

        $arrayState = $column->getStateUsing === null
            ? $state
            : $this->evaluate($column->getStateUsing->getClosure(), [
                'column' => $column->tableColumn,
                'livewire' => $this->getLivewire(),
                'record' => $record,
            ]);

        if ($this->columnsSource === 'table' && $column->tableColumn !== null && is_string($arrayState) && ($separator = $column->tableColumn->getSeparator())) {
            $arrayState = explode($separator, $arrayState);
            $arrayState = (count($arrayState) === 1 && blank($arrayState[0])) ?
                [] :
                $arrayState;
        }

        if (is_bool($arrayState)) {
            return (int) $arrayState;
        }

        return $arrayState;
    }

    private function applyFormatStateUsing(Column $column, $record, $state)
    {
        $formattedState = [];

        if ($this->shouldIgnoreFormattingForColumn($column)) {
            return $state;
        }

        foreach (Arr::wrap($state) as $state) {
            $state = $column->formatStateUsing === null
                ? $state
                : $this->evaluate($column->formatStateUsing->getClosure(), [
                    'column' => $column->tableColumn,
                    'livewire' => $this->getLivewire(),
                    'record' => $record,
                    'state' => $state,
                ]);

            $formattedState[] = $state;
        }

        return $formattedState;
    }
}
