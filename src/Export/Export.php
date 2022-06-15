<?php

namespace pxlrbt\FilamentExcel\Export;

use Arr;
use Filament\Facades\Filament;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Component;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings as HasHeadings;
use Maatwebsite\Excel\Concerns\WithMapping as HasMapping;

use pxlrbt\FilamentExcel\Concerns\Except;
use pxlrbt\FilamentExcel\Concerns\Only;
use pxlrbt\FilamentExcel\Concerns\WithChunkCount;
use pxlrbt\FilamentExcel\Concerns\WithFields;
use pxlrbt\FilamentExcel\Concerns\WithFilename;
use pxlrbt\FilamentExcel\Concerns\WithHeadings;
use pxlrbt\FilamentExcel\Concerns\WithWriterType;
use pxlrbt\FilamentExcel\Interactions\AskForFilename;
use pxlrbt\FilamentExcel\Interactions\AskForWriterType;
use pxlrbt\FilamentExcel\SendCompletedNotificationJob;

class Export implements HasMapping, HasHeadings, FromQuery
{
    use Exportable {
        Exportable::download as downloadExport;
        Exportable::queue as queueExport;
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

    protected string $name;

    protected ?string $label = null;

    protected array $formSchema = [];

    protected ?array $formData;

    public string $model;

    public ?string $modelKeyName;

    protected array $recordIds = [];

    protected bool $isQueued = false;

    protected ?Component $livewire = null;

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

    public function getLivewire()
    {
        return $this->livewire;
    }

    protected function getQuery(): ?Builder
    {
        // if ($this->query !== null) {
        //     return $this->query;
        // }
        //
        // if (method_exists($this->getLivewire(), 'getFilteredTableQuery')) {
        //     return invade($this->getLivewire())?->getFilteredTableQuery();
        // }

        return $this->getModelClass()::query()
            ->when(
                filled($this->recordIds),
                fn ($query) => $query->whereIntegerInRaw($this->modelKeyName, $this->recordIds)
            );
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
        return $this->model ??= $this->getResource()::getModel();
    }

    public function getMapping($row): array
    {
        $keys = collect($this->getFields())->mapWithKeys(fn ($key) => [$key => $key]);

        $only = $this->getOnly();
        $except = $this->getExcept();

        if ($row instanceof Model) {
            // If user didn't specify a custom except array, use the hidden columns.
            // User can override this by passing an empty array ->except([])
            // When user specifies with only(), ignore if the column is hidden or not.
            if ($except === null && (! is_array($only) || count($only) === 0)) {
                $except = $row->getHidden();
            }
        }

        if (is_array($only) && count($only) > 0) {
            $keys = $keys->only($only);
        }

        if (is_array($except) && count($except) > 0) {
            $keys = $keys->except($except);
        }

        return $keys->toArray();
    }

    /**
     * @param  Model|mixed  $row
     */
    public function map($row): array
    {
        $result = [];

        if ($row instanceof Model) {
            $row->setHidden([]);
        }

        foreach ($this->getMapping($row) as $key) {
            $result[$key] = data_get($row, $key);
        }

        return $result;
    }

    public function query()
    {
        return $this->getQuery();
    }

    protected function getRecordIds(): array
    {
        return $this->recordIds;
    }


    public function hydrate($livewire = null, $records = null, $formData = null): static
    {
        $this->livewire = $livewire;
        $this->modelKeyName = $this->getModelInstance()->getKeyName();
        $this->recordIds = $this->getModelInstance()->pluck($this->modelKeyName)->toArray() ?? [];
        $this->formData = $formData;

        return $this;
    }

    protected function getModelInstance()
    {
        return $this->query()->first();
    }

    public function export()
    {
        $this->extractFilename();
        $this->extractWriterType();

        if (! $this->isQueued()) {
            return $this->downloadExport($this->getFilename(), $this->getWriterType());
        }

        $this->evaluateClosures();

        $filename = Str::uuid() . '-' . $this->getFilename();
        $writerType = $this->getWriterType();

        unset($this->livewire);
        unset($this->formSchema);

        $this
            ->queueExport($filename, 'filament-excel', $writerType)
            ->chain([new SendCompletedNotificationJob(auth()->id(), $filename)]);

        Filament::notify('success', 'Export queued');
    }

    public function queue(): static
    {
        $this->isQueued = true;

        return $this;
    }

    protected function isQueued()
    {
        return $this->isQueued;
    }

    public function headings(): array
    {
        if ($this->isQueued()) {
           return $this->headings;
        }

        $keys = $this->getMapping($this->getModelInstance());

        return $this->mergeNumericArray(
            $keys,
            Arr::only($this->getHeadings(), $keys),
        );
    }


    protected function evaluateClosures()
    {
        $this->except = $this->getExcept();
        $this->only = $this->getOnly();
        $this->fields = $this->getFields();

        $this->model = $this->getModelClass();

        // Headings
        $keys = $this->getMapping($this->getModelInstance());

        $this->headings = $this->mergeNumericArray(
            $keys,
            Arr::only($this->getHeadings(), $keys),
        );

        $this->autoHeadings = [];
        $this->autoFields = [];
    }


    protected function getDefaultEvaluationParameters(): array
    {
        return [
            'livewire' => $this->getLivewire(),
            'resource' => $this->getResource(),
            'recordIds' => $this->getRecordIds(),
            'query' => $this->getQuery(),
            'model' => $this->getModelClass(),
        ];
    }
}
