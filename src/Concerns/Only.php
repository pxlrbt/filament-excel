<?php

namespace pxlrbt\FilamentExcel\Concerns;

trait Only
{
    protected array $only = [];

    protected string $fieldSource = 'table';

    public function only(array|string $columns): self
    {
        $this->only = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    public function onlyTableFields(): self
    {
        $this->fieldSource = 'table';

        return $this;
    }

    public function onlyFormFields(): self
    {
        $this->fieldSource = 'form';

        return $this;
    }

    public function allFields(): self
    {
        $this->fieldSource = 'all';

        return $this;
    }

    public function getFieldSource(): string
    {
        return $this->fieldSource;
    }

    public function getOnly(): array
    {
        return $this->only;
    }
}
