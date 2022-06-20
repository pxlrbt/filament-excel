<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

trait CanQueue
{
    protected bool $isQueued = false;

    public function queue(): static
    {
        $this->isQueued = true;

        return $this;
    }

    protected function isQueued()
    {
        return $this->isQueued;
    }

    protected function prepareQueuedExport()
    {
        // Evaluate
        $this->except = $this->getExcept();
        $this->only = $this->getOnly();
        $this->columns = $this->getColumns();

        $this->model = $this->getModelClass();
        $this->headings = $this->getHeadings();

        $this->filename = $this->getFilename();
        $this->writerType = $this->getWriterType();
        $this->livewireClass = $this->getLivewireClass();

        // Reset
        $this->generatedColumns = [];
        $this->formSchema = [];

        $this->livewire = null;
    }
}
