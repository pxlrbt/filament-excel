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
    public function map($row): array
    {
        $result = [];
        $columns = $this->getMapping($row);

        if ($row instanceof Model) {
            $row->setHidden([]);
        }

        foreach ($columns as $column) {
            $key = $column->getName();
            $entry = data_get($row, $key);

            $entry = $this->evaluate($column->formatStateUsing->getClosure(), ['state' => $entry, 'row' => $row]);

            if (is_object($entry)) {
                $entry = match (true) {
                    method_exists($entry, 'toString') => $entry->toString(),
                    method_exists($entry, '__toString') => $entry->__toString(),
                    function_exists('enum_exists') && $entry instanceof UnitEnum => $entry->value,
                };
            }

            $result[$key] = $entry;
        }

        return $result;
    }
}
