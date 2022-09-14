<?php

namespace pxlrbt\FilamentExcel\Interactions;

use Filament\Forms\Components\TextInput;

trait AskForFilename
{
    public function askForFilename(?string $default = null, ?string $label = null, ?callable $callback = null): self
    {
        $field = TextInput::make('filename')
            ->label($label ?? __('filament-excel::fields.filename'))
            ->default($default ?? '')
            ->required();

        if (is_callable($callback)) {
            $callback($field);
        }

        $this->formSchema[] = $field;

        return $this;
    }
}
