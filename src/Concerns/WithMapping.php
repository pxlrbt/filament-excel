<?php

namespace pxlrbt\FilamentExcel\Concerns;

use Illuminate\Database\Eloquent\Model;

trait WithMapping
{
    public function getMapping($row): array
    {
        $keys = collect($this->getFields())->mapWithKeys(fn ($key) => [$key => $key]);

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
            $keys = $keys->only($only);
        }

        if (is_array($except) && count($except) > 0) {
            $keys = $keys->except($except);
        }

        return $keys->toArray();
    }

    /**
     * @param  Model|mixed  $row
     */
    public function map($row): array
    {
        $result = [];

        if ($row instanceof Model) {
            $row->setHidden([]);
        }

        foreach ($this->getMapping($row) as $key) {
            $result[$key] = data_get($row, $key);
        }

        return $result;
    }
}
