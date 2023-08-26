<?php

namespace pxlrbt\FilamentExcel\Columns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Tables\Columns\Column as TableColumn;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use ReflectionClass;

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

    public function getName()
    {
        return $this->name;
    }

    public function heading(Closure|string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function getHeading()
    {
        return $this->heading ?? Str::headline($this->name);
    }

    public function width(Closure|int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getWidth()
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

        // Try to remove all closures
        foreach ((new ReflectionClass($clone))->getProperties() as $property) {
            $property->setAccessible(true);
            $type = (string) $property->getType();

            if (strpos($type, 'Closure') !== false) {
                if (strpos($type, 'null') !== false || strpos($type, '?') !== false) {
                    $property->setValue($clone, null);
                }
            }
        }

        // Reset other properties
        $clone->table(null);
        $clone->getStateUsing(null);
        invade($clone)->summarizers = [];

        $this->tableColumn = $clone;

        return $this;
    }

    public function getFormat()
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
