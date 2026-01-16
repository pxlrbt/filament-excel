<?php

namespace pxlrbt\FilamentExcel\Columns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Tables\Columns\Column as TableColumn;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionClass;
use Throwable;

class Column
{
    use EvaluatesClosures;

    public string $name;

    public Closure|string|null $heading = null;

    public Closure|int|null $width = null;

    public Closure|string|null $format = null;

    public ?TableColumn $tableColumn = null;

    public ?SerializableClosure $getStateUsing = null;

    public ?SerializableClosure $formatStateUsing = null;

    protected function __construct($name)
    {
        $this->name = $name;
    }

    public static function make($name): static
    {
        $static = new static($name);
        $static->formatStateUsing(fn ($state) => $state);
        $static->setUp();

        return $static;
    }

    public function setUp()
    {
        //
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function heading(Closure|string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function getHeading(): string|Closure
    {
        return $this->heading ?? Str::headline($this->name);
    }

    public function width(Closure|int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getWidth(): int|Closure|null
    {
        return $this->width;
    }

    public function format(Closure|string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function tableColumn(TableColumn $tableColumn): static
    {
        $clone = clone $tableColumn;

        // Remove all closures from cloned TableColumn for queue serialization
        $properties = (new ReflectionClass($clone))->getProperties();
        foreach ($properties as $property) {
            $property->setAccessible(true);
            try {
                $value = $property->getValue($clone);
                $property->setValue($clone, $this->removeClosuresFromValue($value));
            } catch (Throwable $e) {
                // Skip properties that can't be accessed or modified
            }
        }
        // Reset properties that reference non-serializable objects
        $clone->layout(null);
        $clone->action(null);
        $clone->table(null);
        $clone->summarize([]);

        $this->tableColumn = $clone;

        return $this;
    }

    /**
     * Recursively remove closures from an value.
     */
    protected function removeClosuresFromValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($value) => $this->removeClosuresFromValue($value), $value);
        }

        if ($value instanceof Closure) {
            return null;
        }

        return $value;

    }

    public function getFormat(): string|Closure|null
    {
        return $this->format;
    }

    public function getStateUsing(?callable $callback): static
    {
        $this->getStateUsing = $callback ? new SerializableClosure($callback) : null;

        return $this;
    }

    public function formatStateUsing(?callable $callback): static
    {
        $this->formatStateUsing = $callback ? new SerializableClosure($callback) : null;

        return $this;
    }
}
