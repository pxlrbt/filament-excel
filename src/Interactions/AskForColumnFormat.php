<?php

namespace pxlrbt\FilamentExcel\Interactions;

use Filament\Forms\Components\Select;
use Illuminate\Support\Arr;
use Laravel\SerializableClosure\SerializableClosure;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Columns\ColumnFormats;

trait AskForColumnFormat
{
    protected SerializableClosure | array $availableFormats = [];

    public function askForFormat(?array $default = null, ?string $label = null, ?callable $callback = null, ?callable $formatCallback = null, SerializableClosure | string | array $availableFormats = null): static
    {
        $formatCallback ??= fn () => Arr::get($this->formData, 'format');

        $field = Select::make('format')
            ->label($label ?? __('filament-excel::fields.column_format'))
            ->default($default ?? NumberFormat::FORMAT_GENERAL)
            ->options(fn() => $this->getAvailableFormats());

        if (is_callable($callback)) {
            $callback($field);
        }

        $this
            ->availableFormats($availableFormats)
            ->format($formatCallback)
            ->form([$field]);

        return $this;
    }

    public function availableFormats(SerializableClosure | string | array $availableFormats): static
    {
        $availableFormats ??= ColumnFormats::getAllOptions();
        if (is_string($availableFormats)) {
            $availableFormats = match ($availableFormats) {
                ColumnFormats::NUMBER => ColumnFormats::getNumberOptions(),
                ColumnFormats::DATE => ColumnFormats::getDateOptions(),
                ColumnFormats::DATETIME => ColumnFormats::getDateTimeOptions(),
                ColumnFormats::PERCENTAGE => ColumnFormats::getPercentageOptions(),
                default => ColumnFormats::getAllOptions(),
            };
        }

        $this->availableFormats = $availableFormats;

        return $this;
    }

    public function getAvailableFormats(): array
    {
        return $this->evaluate($this->availableFormats);
    }
}
