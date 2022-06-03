<?php

namespace pxlrbt\FilamentExcel\Export;

use Filament\Resources\Resource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Component;
use Maatwebsite\Excel\Concerns\Exportable;
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

class BulkExport extends Export implements FromCollection, WithCustomChunkSize, WithHeadingsConcern, WithMapping
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
    use Exportable;

    protected ?Component $livewire;

    protected ?Collection $records;

    protected ?string $model;

    protected ?array $formData;

    public function hydrate($livewire = null, $records = null, $formData = null): static
    {
        $this->livewire = $livewire;
        $this->records = $records;
        $this->formData = $formData;

        return $this;
    }

    public function getLivewire()
    {
        return $this->livewire;
    }

    public function getRecords(): ?Collection
    {
        return $this->records;
    }

    protected function getQuery(): ?Builder
    {
        if ($this->getLivewire() === null) {
            return null;
        }

        return invade($this->getLivewire())->getFilteredTableQuery();
    }

    protected function getResource()
    {
        $livewire = $this->getLivewire();

        if (! method_exists($livewire, 'getResource')) {
            return null;
        }

        return $this->getLivewire()::getResource();
    }

    public function getModel(): ?string
    {
        return get_class($this->records->first());
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

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        $keys = $this->getMapping($this->getRecords()->first());

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
            'records' => $this->getRecords(),
            'query' => $this->getQuery(),
            'model' => $this->getModel(),
        ];
    }
}
