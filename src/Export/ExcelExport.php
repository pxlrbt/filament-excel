<?php

namespace pxlrbt\FilamentExcel\Export;

use Filament\Facades\Filament;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Component;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings as HasHeadings;
use Maatwebsite\Excel\Concerns\WithMapping as HasMapping;
use pxlrbt\FilamentExcel\Concerns\CanQueue;
use pxlrbt\FilamentExcel\Concerns\Except;
use pxlrbt\FilamentExcel\Concerns\Only;
use pxlrbt\FilamentExcel\Concerns\WithChunkCount;
use pxlrbt\FilamentExcel\Concerns\WithFields;
use pxlrbt\FilamentExcel\Concerns\WithFilename;
use pxlrbt\FilamentExcel\Concerns\WithHeadings;
use pxlrbt\FilamentExcel\Concerns\WithMapping;
use pxlrbt\FilamentExcel\Concerns\WithWriterType;
use pxlrbt\FilamentExcel\Interactions\AskForFilename;
use pxlrbt\FilamentExcel\Interactions\AskForWriterType;
use pxlrbt\FilamentExcel\SendCompletedNotificationJob;

class ExcelExport implements HasMapping, HasHeadings, FromQuery
{
    use Exportable, CanQueue  {
        Exportable::download as downloadExport;
        Exportable::queue as queueExport;
        CanQueue::queue insteadof Exportable;
    }

    use EvaluatesClosures;
    use AskForFilename;
    use AskForWriterType;
    use Except;
    use Only;
    use WithChunkCount;
    use WithFields;
    use WithFilename;
    use WithHeadings;
    use WithWriterType;
    use WithMapping;

    protected string $name;

    protected ?string $label = null;

    protected ?Component $livewire = null;

    protected array $formSchema = [];

    protected ?array $formData;

    public ?string $model = null;

    public ?string $modelKeyName;

    protected array $recordIds = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public static function make(string $name = 'export'): static
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

    public function getLivewire(): Component
    {
        return $this->livewire;
    }

    public function getRecordIds(): array
    {
        return $this->recordIds;
    }

    protected function getModelInstance(): Model
    {
        return $this->query()->first();
    }

    protected function getResource(): ?string
    {
        if (isset($this->resource)) {
            return $this->resource;
        }

        $livewire = $this->getLivewire();

        if (! method_exists($livewire, 'getResource')) {
            return null;
        }

        return $this->getLivewire()::getResource();
    }

    public function getModelClass(): ?string
    {
        if ($this->model !== null) {
            return $this->model;
        }

        if (($resource = $this->getResource()) !== null) {
            $model = $resource::getModel();
        } elseif (($livewire = $this->getLivewire()) instanceof HasTable) {
            $model = $livewire->getTableModel();
        }

        return $this->model ??= $model;
    }

    public function hydrate($livewire = null, $records = null, $formData = null): static
    {
        $this->livewire = $livewire;
        $this->modelKeyName = $this->getModelInstance()->getKeyName();
        $this->recordIds = $this->getModelInstance()->pluck($this->modelKeyName)->toArray() ?? [];
        $this->formData = $formData;

        return $this;
    }

    public function export()
    {
        $this->resolveFilename();
        $this->resolveWriterType();

        if (! $this->isQueued()) {
            return $this->downloadExport($this->getFilename(), $this->getWriterType());
        }

        $this->prepareQueuedExport();

        $filename = Str::uuid() . '-' . $this->getFilename();

        $this
            ->queueExport($filename, 'filament-excel', $this->getWriterType())
            ->chain([new SendCompletedNotificationJob(auth()->id(), $filename)]);

        Filament::notify('success', __('Export queued'));
    }

    public function query(): Builder
    {
        return $this->getModelClass()::query()
            ->when(
                filled($this->recordIds),
                fn ($query) => $query->whereIntegerInRaw($this->modelKeyName, $this->recordIds)
            );
    }

    public function headings(): array
    {
        return $this->resolveHeadings();
    }

    protected function getDefaultEvaluationParameters(): array
    {
        return [
            'livewire' => $this->getLivewire(),
            'resource' => $this->getResource(),
            'recordIds' => $this->getRecordIds(),
            'query' => $this->query(),
            'model' => $this->getModelClass(),
        ];
    }
}
