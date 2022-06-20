<?php

namespace pxlrbt\FilamentExcel\Columns;

use Closure;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\SerializableClosure;

class Column
{
    use EvaluatesClosures;

    public string $name;

    public Closure | string | null $heading = null;

    public Closure | int | null $width = null;

    public Closure | string | null $format = null;

    public SerializableClosure $formatStateUsing;

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

    public function heading(Closure | string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function getHeading()
    {
        return $this->heading ?? Str::headline($this->name);
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

    public function formatStateUsing(callable $callback): static
    {
        $this->formatStateUsing = new SerializableClosure($callback);

        return $this;
    }
}
