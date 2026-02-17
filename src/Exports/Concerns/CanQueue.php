<?php

namespace pxlrbt\FilamentExcel\Exports\Concerns;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use pxlrbt\FilamentExcel\Dev\FindClosures;

trait CanQueue
{
    protected bool $isQueued = false;

    protected ?string $queueName = null;

    protected ?string $queueConnection = null;

    public function queue(?string $queue = null, ?string $connection = null): static
    {
        $this->isQueued = true;
        $this->queueName = $queue;
        $this->queueConnection = $connection;

        return $this;
    }

    protected function isQueued()
    {
        return $this->isQueued;
    }

    public function getQueueName(): ?string
    {
        return $this->queueName;
    }

    public function getQueueConnection(): ?string
    {
        return $this->queueConnection;
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

        // Debug Closured
        // $closures = (new FindClosures)($this);
        // dd($closures);
    }
}
