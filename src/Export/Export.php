<?php

namespace pxlrbt\FilamentExcel\Export;

use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Exportable;

abstract class Export
{
    use Exportable;
    use EvaluatesClosures;

    protected string $name;

    protected ?string $label = null;

    protected array $formSchema = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
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

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label ?? Str::headline($this->name);
    }

    public function getFormSchema(): array
    {
        return $this->formSchema;
    }
}
