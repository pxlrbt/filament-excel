<?php

namespace pxlrbt\FilamentExcel\Concerns;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;

trait WithHeadings
{
    protected ?Closure $headingCallback = null;

    public function withHeadings(array|string|null $headings = null): self
    {
        $headings = is_array($headings) ? $headings : func_get_args();

        if (0 === count($headings)) {
            $this->headingCallback = $this->autoHeading();

            return $this;
        }

        $this->headingCallback = function () use ($headings) {
            return $headings;
        };

        return $this;
    }

    public function headings(): array
    {
        return is_callable($this->headingCallback)
            ? ($this->headingCallback)()
            : [];
    }

    // protected function handleHeadings()
    // {
    //     if (is_callable($this->headingCallback)) {
    //         $this->headings = ($this->headingCallback)();
    //     }
    // }

    /**
     * @return callable
     */
    protected function autoHeading(): callable
    {
        $component = $this;

        return function () use ($component) {
            $map = $component->getFieldMapping();

            /**
             * @var Model
             */
            $model = $component->records->first();

            if (! $model) {
                return [];
            }

            $attributes = collect($component->map($model))->keys();

            // Attempt to replace the attribute name by the resource field name.
            // Fallback to the attribute name, when none is found.
            return $attributes->map(
                fn ($attribute) => $map->get($attribute, $attribute)
            )->toArray();
        };
    }
}
