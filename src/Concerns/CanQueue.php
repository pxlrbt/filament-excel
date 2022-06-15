<?php

namespace pxlrbt\FilamentExcel\Concerns;

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
        $this->fields = $this->getFields();

        $this->model = $this->getModelClass();
        $this->filename = $this->getFilename();
        $this->writerType = $this->getWriterType();

        $this->headings = $this->resolveHeadings();

        // Reset
        $this->autoHeadings = [];
        $this->autoFields = [];

        $this->livewire = null;
        $this->formSchema = [];
    }
}
