<?php

namespace pxlrbt\FilamentExcel\Interactions;

use Filament\Forms\Components\Checkbox;

trait AskForConfirmation
{
    public function askForConfirmation(string $label = null, string $helperText = null, callable $callback = null): self
    {
        $field = Checkbox::make('confirm')
            ->label($label ?? __('Confirm'))
            ->helperText($helperText ?? 'Please confirm that you want to export all records.')
            ->required();

        if (is_callable($callback)) {
            $callback($field);
        }

        $this->formSchema[] = $field;

        return $this;
    }
}
