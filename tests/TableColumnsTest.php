<?php

use Filament\Tables\Columns\TextColumn;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Tests\Fixtures\Post;
use pxlrbt\FilamentExcel\Tests\Fixtures\PostsTable;

beforeEach(function () {
    Post::create([
        'title' => 'Hello world',
        'date_format' => 'd/m/Y',
        'currency' => 'eur',
        'price' => 1234.5,
        'views' => 4200,
        'is_published' => true,
        'published_at' => '2026-04-07 09:27:50',
    ]);
});

function exportColumn(TextColumn $column, ?string $defaultCurrency = null): mixed
{
    PostsTable::$columnsUnderTest = [$column];
    PostsTable::$defaultCurrency = $defaultCurrency;

    $livewire = new PostsTable;
    $livewire->bootedInteractsWithTable();

    $export = ExcelExport::make()
        ->fromTable()
        ->hydrate($livewire);

    return $export->map(Post::first())[$column->getName()];
}

it('exports a plain column', function () {
    expect(exportColumn(TextColumn::make('title')))->toBe('Hello world');
});

it('exports a date column', function () {
    expect(exportColumn(TextColumn::make('published_at')->date()))->toBe('Apr 7, 2026');
});

it('exports a date column with an explicit format', function () {
    expect(exportColumn(TextColumn::make('published_at')->date('Y-m-d')))->toBe('2026-04-07');
});

// Regression test for #265: dateTime() defaults the format to a closure reading it off
// the table, which is gone by the time the export runs.
it('exports a dateTime column', function () {
    expect(exportColumn(TextColumn::make('published_at')->dateTime()))->toBe('Apr 7, 2026 09:27:50');
});

it('exports a time column', function () {
    expect(exportColumn(TextColumn::make('published_at')->time()))->toBe('09:27:50');
});

it('exports an isoDate column', function () {
    expect(exportColumn(TextColumn::make('published_at')->isoDate()))->toBe('04/07/2026');
});

it('exports a money column', function () {
    expect(exportColumn(TextColumn::make('price')->money()))->toBe('$1,234.50');
});

it('exports a money column with an explicit currency', function () {
    expect(exportColumn(TextColumn::make('price')->money('eur')))->toBe('€1,234.50');
});

// money() leaves the currency null and reads it off the table, which the export no
// longer has — so the table default has to be resolved up front.
it('exports a money column with the currency of the table', function () {
    expect(exportColumn(TextColumn::make('price')->money(), defaultCurrency: 'eur'))->toBe('€1,234.50');
});

it('exports a money column whose currency depends on the record', function () {
    expect(exportColumn(TextColumn::make('price')->money(fn (Post $record) => $record->currency)))
        ->toBe('€1,234.50');
});

it('exports a numeric column', function () {
    expect(exportColumn(TextColumn::make('views')->numeric()))->toBe('4,200');
});

it('exports a boolean column as an integer', function () {
    expect(exportColumn(TextColumn::make('is_published')))->toBe('1');
});

it('exports a column with a custom state', function () {
    expect(exportColumn(TextColumn::make('title')->getStateUsing(fn (Post $record) => strtoupper($record->title))))
        ->toBe('HELLO WORLD');
});

it('exports a column with a custom format', function () {
    expect(exportColumn(TextColumn::make('title')->formatStateUsing(fn (string $state) => "[{$state}]")))
        ->toBe('[Hello world]');
});

// Formats that depend on the record can only be resolved per row, so they must survive
// the eager evaluation that fixes #265.
it('exports a date column whose format depends on the record', function () {
    expect(exportColumn(TextColumn::make('published_at')->date(fn (Post $record) => $record->date_format)))
        ->toBe('07/04/2026');
});

// Regression test for #280: setAccessible() is deprecated since PHP 8.5.
it('exports a column without deprecations from the package', function () {
    $deprecations = [];
    set_error_handler(function (int $errno, string $errstr, string $errfile) use (&$deprecations) {
        if (str_starts_with(realpath($errfile), realpath(__DIR__.'/../src'))) {
            $deprecations[] = $errstr;
        }

        return true;
    }, E_DEPRECATED);

    try {
        exportColumn(TextColumn::make('title'));
    } finally {
        restore_error_handler();
    }

    expect($deprecations)->toBe([]);
});
