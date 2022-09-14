<?php

namespace pxlrbt\FilamentExcel\Columns;

use Closure;
use Filament\Support\Concerns\Configurable;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Tables\Columns\Column as TableColumn;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;
use pxlrbt\FilamentExcel\Interactions\AskForColumnFormat;
use ReflectionClass;
use pxlrbt\FilamentExcel\Concerns\HasForm;
use pxlrbt\FilamentExcel\Concerns\HasLabel;
use pxlrbt\FilamentExcel\Concerns\HasName;

class Column
{
    use Configurable;
    use EvaluatesClosures;
    use AskForColumnFormat;
    use HasLabel;
    use HasName;
    use HasForm;

    public Closure | int | null $width = null;

    public Closure | string | null $format = null;

    public ?TableColumn $tableColumn = null;

    public ?SerializableClosure $getStateUsing = null;

    public ?SerializableClosure $formatStateUsing = null;

    protected function __construct($name)
    {
        $this->name($name)
            ->label(Str::headline($this->name));
    }

    public static function make($name): static
    {
        $static = new static($name);
        $static->formatStateUsing(fn ($state) => $state);
        $static->configure();

        return $static;
    }

    /**
     * @deprecated use label() instead
     */
    public function heading(Closure | string $heading): static
    {
        return $this->label($heading);
    }

    /**
     * @deprecated use getLabel() instead
     */
    public function getHeading()
    {
        return $this->getLabel();
    }

    public function width(Closure | int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function format(Closure | string $format): static
    {
        $this->format = $format;

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

    public function tableColumn(TableColumn $tableColumn): static
    {
        // Try to remove all closures
        foreach ((new ReflectionClass($tableColumn))->getProperties() as $property) {
            $property->setAccessible(true);
            $type = (string) $property->getType();

            if (strpos($type, 'Closure') !== false) {
                if (strpos($type, 'null') !== false || strpos($type, '?') !== false) {
                    $property->setValue($tableColumn, null);
                }
            }
        }

        // $tableColumn->getStateUsing(null);
        // $tableColumn->formatStateUsing(null);

        $this->tableColumn = $tableColumn;

        return $this;
    }
}
