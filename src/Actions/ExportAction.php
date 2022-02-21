<?php

namespace pxlrbt\FilamentExcel\Actions;

use Closure;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings as WithHeadingsConcern;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Concerns\Except;
use pxlrbt\FilamentExcel\Concerns\Only;
use pxlrbt\FilamentExcel\Concerns\ResolvesFieldsFromResource;
use pxlrbt\FilamentExcel\Concerns\WithChunkCount;
use pxlrbt\FilamentExcel\Concerns\WithExportable;
use pxlrbt\FilamentExcel\Concerns\WithFilename;
use pxlrbt\FilamentExcel\Concerns\WithHeadings;
use pxlrbt\FilamentExcel\Concerns\WithWriterType;
use pxlrbt\FilamentExcel\Interactions\AskForFilename;
use pxlrbt\FilamentExcel\Interactions\AskForWriterType;

class ExportAction extends BulkAction implements FromCollection, WithCustomChunkSize, WithHeadingsConcern, WithMapping
{
    use AskForFilename;
    use AskForWriterType;
    use ResolvesFieldsFromResource;
    use Except;
    use Only;
    use WithChunkCount;
    use WithExportable;
    use WithFilename;
    use WithHeadings;
    use WithWriterType;

    protected Resource $resource;

    protected ?string $model;

    protected ?Collection $records;

    protected function setUp(): void
    {
        $this->modalWidth = 'sm';
        $this->action(Closure::fromCallable([$this, 'export']));
    }

    protected function export(Collection $records, array $data)
    {
        $this->resource = new ($this->getLivewire()::getResource());
        $this->records = $records;
        $this->model = get_class($records->first());

        $this->handleFilename($data);
        $this->handleWriterType($data);

        return Excel::download(
            $this->getExportable(),
            $this->getFilename(),
            $this->getWriterType(),
        );
    }

    public function collection(): Collection
    {
        return $this->records;
    }

    /**
     * @param  Model|mixed  $row
     */
    public function map($row): array
    {
        $only = $this->getOnly();
        $except = $this->getExcept();

        if ($row instanceof Model) {
            // If user didn't specify a custom except array, use the hidden columns.
            // User can override this by passing an empty array ->except([])
            // When user specifies with only(), ignore if the column is hidden or not.
            if ($except === null && (! is_array($only) || count($only) === 0)) {
                $except = $row->getHidden();
            }

            // Make all attributes visible
            $row->setHidden([]);
            if ($this->getFieldSource() !== 'all') {
                $row = $row->only($this->getFieldMapping()->keys()->toArray());
            } else {
                $row = $row->toArray();
            }
        }

        if (is_array($only) && count($only) > 0) {
            $row = Arr::only($row, $only);
        }

        if (is_array($except) && count($except) > 0) {
            $row = Arr::except($row, $except);
        }

        return $row;
    }

    protected function getDefaultExtension(): string
    {
        return $this->getWriterType() ? strtolower($this->getWriterType()) : 'xlsx';
    }
}
