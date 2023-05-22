<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;

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
        $this->columnFormats = $this->getColumnFormats();
        $this->columnWidths = $this->getColumnWidths();
        $this->livewireClass = $this->getLivewireClass();

        // Reset
        $this->generatedColumns = [];
        $this->formSchema = [];

        if (isset($this->livewire->ownerRecord)) {
            $this->livewireOwnerRecord = $this->livewire->ownerRecord;
        }

        $this->livewire = null;
        $this->query = EloquentSerializeFacade::serialize($this->query());
    }
}
