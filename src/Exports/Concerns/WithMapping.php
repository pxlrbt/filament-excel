<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use UnitEnum;

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
     * @param  Model|mixed  $row
     */
    public function map($record): array
    {
        $result = [];
        $columns = $this->getMapping($record);

        if ($record instanceof Model) {
            $record->setHidden([]);
        }

        foreach ($columns as $column) {
            $key = $column->getName();
            $state = data_get($record, $key);

            $state = $column->getStateUsing === null
                ? $state
                : $this->evaluate($column->getStateUsing->getClosure(), [
                    'column' => $column->tableColumn,
                    'livewire' => $this->getLivewire(),
                    'record' => $record,
                    'state' => $state,
                ]);

            $state = $column->formatStateUsing === null
                ? $state
                : $this->evaluate($column->formatStateUsing->getClosure(), [
                    'column' => $column->tableColumn,
                    'livewire' => $this->getLivewire(),
                    'record' => $record,
                    'state' => $state,
                ]);

            if (is_object($state)) {
                $state = match (true) {
                    method_exists($state, 'toString') => $state->toString(),
                    method_exists($state, '__toString') => $state->__toString(),
                    function_exists('enum_exists') && $state instanceof UnitEnum => $state->value,
                };
            }

            $result[$key] = $state;
        }

        return $result;
    }
}
