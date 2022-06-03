<?php

namespace pxlrbt\FilamentExcel\Export;

use Filament\Resources\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings as WithHeadingsConcern;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Concerns\Except;
use pxlrbt\FilamentExcel\Concerns\Only;
use pxlrbt\FilamentExcel\Concerns\WithChunkCount;
use pxlrbt\FilamentExcel\Concerns\WithFields;
use pxlrbt\FilamentExcel\Concerns\WithFilename;
use pxlrbt\FilamentExcel\Concerns\WithHeadings;
use pxlrbt\FilamentExcel\Concerns\WithWriterType;
use pxlrbt\FilamentExcel\Interactions\AskForFilename;
use pxlrbt\FilamentExcel\Interactions\AskForWriterType;

class SingleExport extends Export implements FromArray, WithCustomChunkSize, WithHeadingsConcern, WithMapping
{
    use AskForFilename;
    use AskForWriterType;
    use Except;
    use Only;
    use WithChunkCount;
    use WithFields;
    use WithFilename;
    use WithHeadings;
    use WithWriterType;

    protected Resource $resource;

    protected Model $record;

    protected ?string $model;

    protected ?array $formData;

    public function hydrate($livewire, $record, $formData): static
    {
        $this->livewire = $livewire;
        $this->record = $record;
        $this->formData = $formData;

        return $this;
    }

    public function getLivewire()
    {
        return $this->livewire;
    }

    public function getRecord(): Model
    {
        return $this->record;
    }

    protected function getResource()
    {
        return $this->getLivewire()::getResource();
    }

    public function getModel(): ?string
    {
        return get_class($this->record);
    }

    public function export()
    {
        $this->extractFilename();
        $this->extractWriterType();

        return Excel::download(
            $this,
            $this->getFilename(),
            $this->getWriterType(),
        );
    }

    public function array(): array
    {
        return [$this->getRecord()];
    }

    public function headings(): array
    {
        $keys = $this->getMapping($this->getRecord()->first());

        return $this->mergeNumericArray(
            $keys,
            Arr::only($this->getHeadings(), $keys),
        );
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

    protected function getDefaultExtension(): string
    {
        return $this->getWriterType() ? strtolower($this->getWriterType()) : 'xlsx';
    }

    protected function getDefaultEvaluationParameters(): array
    {
        return [
            'livewire' => $this->getLivewire(),
            'resource' => $this->getResource(),
            'record' => $this->getRecord(),
            'model' => $this->getModel(),
        ];
    }
}
