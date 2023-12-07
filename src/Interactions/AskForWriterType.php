<?php

namespace pxlrbt\FilamentExcel\Interactions;

use Filament\Forms\Components\Select;
use Maatwebsite\Excel\Excel;

trait AskForWriterType
{
    public function askForWriterType(?string $default = null, ?array $options = null, ?string $label = null, ?callable $callback = null): self
    {
        $options = $options ?: [
            Excel::XLS => 'XLS',
            Excel::XLSX => 'XLSX',
            Excel::CSV => 'CSV',
        ];

        $field = Select::make('writer_type')
            ->label($label ?? __('Type'))
            ->options($options)
            ->default($default ?? '')
            ->required();

        if (is_callable($callback)) {
            $callback($field);
        }

        $this->formSchema[] = $field;

        return $this;
    }
}
