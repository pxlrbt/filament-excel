![header](./.github/resources/header.png)

# Filament Excel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pxlrbt/filament-excel.svg?include_prereleases)](https://packagist.org/packages/pxlrbt/filament-excel)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/pxlrbt/filament-excel/code-style.yml?branch=main&label=tests&style=flat-square)
[![Total Downloads](https://img.shields.io/packagist/dt/pxlrbt/filament-excel.svg)](https://packagist.org/packages/pxlrbt/filament-excel)

Easily configure your Excel exports in Filament via a bulk or page action.


https://user-images.githubusercontent.com/22632550/174591523-831df501-76d5-456a-b12e-f6d8316fb673.mp4


## Installation

Install via Composer. This will download the package and [Laravel Excel](https://laravel-excel.com/).

**Requires PHP 8.0 and Filament 2.0**

```bash
composer require pxlrbt/filament-excel
```

### Laravel 9

If composer require fails on Laravel 9 because of the simple-cache dependency, you will have to specify the psr/simple-cache version as ^2.0 in your composer.json to satisfy the PhpSpreadsheet dependency. You can install both at the same time as:

```bash
composer require psr/simple-cache:^2.0 pxlrbt/filament-excel
```

## Quickstart

Starting with v0.2 Filament Excel should work with both `filament/filament` and `filament/tables` packages. The most simple usage is just adding `ExportBulkAction` to your bulk actions.

**Example for admin package**

```php
<?php

namespace App\Filament\Resources;

use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class UserResource extends Resource
{  
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //   
            ])
            ->bulkActions([
                ExportBulkAction::make()
            ]);
    }
}
```

**Example for table package**

```php
<?php

namespace App\Filament\Resources;

use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

public function getTableBulkActions()
{
    return  [
        ExportBulkAction::make()
    ];
}
```

## Usage

Filament Excel comes with three actions you can use:
- `Actions\Tables\ExportBulkAction` for table bulk actions
- `Actions\Tables\ExportAction` for table header actions
- `Actions\Pages\ExportAction` for record pages

Without further configuration they will try to resolve the fields from the table or form definition and output an Excel file.

### Multiple export classes

You can overwrite the default export class and also configure multiple exports with different settings. The user will be shown a modal to select the export class he wants to use.

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make('table')->fromTable(),
    ExcelExport::make('form')->fromForm(),
])
```

### Closure customization

Many of the functions for customising the export class, accept a Closure that gets passed dynamic data:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make('table')->withFilename(fn ($resource) => $resource::getLabel()),
])
```

The following arguments are available:
- `$livewire`: Livewire component (not available for queued exports)
- `$livewireClass`: Livewire component class
- `$resource`: Resource class
- `$model`: Model class
- `$recordIds`: IDs of selected records (Bulk Action)
- `$query`: The builder instance 

### Filename

The filename is set via `->withFilename()`:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    // Pass a string
    ExcelExport::make()->withFilename(date('Y-m-d') . ' - export'),
    
    // Or pass a Closure
    ExcelExport::make()->withFilename(fn ($resource) => $resource::getLabel())
])
```

### Export types

You can set the file type via `->withWriterType()`:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()->withWriterType(\Maatwebsite\Excel\Excel::XLSX),
])
```


### Defining columns

When using `->fromForm()`/`->fromTable()`/`->fromModel()` the columns are resolved from your table or form definition. You can also provide columns manually, append columns or overwrite generated columns.

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

ExportAction::make()->exports([
    ExcelExport::make()->withColumns([
        Column::make('name'),
        Column::make('created_at'),
        Column::make('deleted_at'),
    ]),
])
```

You can also include only a subset of columns (`->only()`) or exclude certain ones (`->except()`):

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()->fromTable()->except([
        'created_at', 'updated_at', 'deleted_at',
    ]),
    
    ExcelExport::make()->fromTable()->only([
        'id', 'name', 'title',
    ]),
])
```

When you neither pass `->only()` nor `->except()` the export will also respect the `$hidden` attributes of your model, for example the `password` on the user model. You can disable this by passing an empty array `->except([])`.

### Headings

When using `->fromForm()`/`->fromTable()`/`->fromModel()` the headings are resolved from your table or form definition. You can also overwrite headings:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

ExportAction::make()->exports([
    ExcelExport::make()->withColumns([
        Column::make('name')->heading('User name'),
        Column::make('email')->heading('Email address'),
        Column::make('created_at')->heading('Creation date'),
    ]),
])
```

If you want to use the column names and don't like the headings auto generated you can use `->withNamesAsHeadings()`. To disable headings entirely you can append `->withoutHeadings()`

### Formatting

Every column can be formatted by providing a Closure. Additional to the default parameters you get access to `$state` and `$record`.

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

ExportAction::make()->exports([
    ExcelExport::make()->withColumns([
        Column::make('email')
            ->formatStateUsing(fn ($state) => str_replace('@', '[at]', $state)),
            
        Column::make('name')
            ->formatStateUsing(fn ($record) => $record->locations->pluck('name')->join(','),
    ]),
])
```

Columns are auto-scaled to fit the content. If you want to overwrite this with a custom column width you can do so:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

ExportAction::make()->exports([
    ExcelExport::make()->withColumns([
        Column::make('email')->width(10)
    ]),
])
```
The underlying package PhpSpreadsheet provides various options for Excel column formatting. Inspect the `NumberFormat` list for the full list.


```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

ExportAction::make()->exports([
    ExcelExport::make()->withColumns([
        Column::make('currecy')->format(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE)
    ]),
])
```


### User input

You can let the user pick a filename and writer type by using `->askForFilename()` and `->askForWriterType()`:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()
        ->askForFilename()
        ->askForWriterType()
])
```

You can also use the users input inside a Closure:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()
        ->askForFilename()
        ->withFilename(fn ($filename) => 'prefix-' . $filename)
])
```

### Modify the query

You can modify the query that is used to retrieve the model by using `->modifyQueryUsing()`:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()
        ->fromTable()
        ->modifyQueryUsing(fn ($query) => $query->where('exportable', true))
])
```

### Queued exports

Exports for resources with many entries can take some time and therefore can be queued with `->queue()`. They will be processed in background jobs and the user will be notified with a notification on the next page load (or when Livewire is polling). 

The temporary file will be deleted after the first download. Files that are not downloaded will be deleted by a scheduled command after 24 hours.

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()->queue()
])
```

The size of exported records per Job can be adjusted by using `->withChunkSize()`:

```php
se pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()->queue()->withChunkSize(100)
])
```


## Custom exports

If you need even more customization or want to clean up your resources by separating the export code, you can extend the ExcelExport class and configure it using `setUp()`:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class CustomExport extends ExcelExport
{
    
    public function setUp()
    {
        $this->withFilename('custom_export');
        $this->withColumns([
            Column::make('name'),
            Column::make('email'),
        ]);
    }
}
```

### User Report Builder

You can use the `->askForColumns()` to let the user build a report. This is especially useful when you have a lot of columns and want to let the user pick the ones they need and customize the display order.

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()->askForColumns()
])
```

You can also create a custom export class and specify the columns where user can select a display format

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Columns\ColumnFormats;

class CustomExport extends ExcelExport
{
    
    public function setUp()
    {
        $this->withFilename('custom_export');
        $this->withColumns([
            Column::make('total')->askForFormat(availableFormats: ColumnFormats::NUMBER),
            Column::make('created_at')->askForFormat(availableFormats: ColumnFormats::DATE),
        ]);
    }
}
```

Then you can you custom export with `->askForColumns()`:

```php
ExportAction::make()->exports([
    CustomExport::make()->askForColumns()
])
```

## Contributing

If you want to contribute to this packages, you may want to test it in a real Filament project:

- Fork this repository to your GitHub account.
- Create a Filament app locally.
- Clone your fork in your Filament app's root directory.
- In the `/filament-excel` directory, create a branch for your fix, e.g. `fix/error-message`.

Install the packages in your app's `composer.json`:

```json
"require": {
    "pxlrbt/filament-excel": "dev-fix/error-message as main-dev",
},
"repositories": [
    {
        "type": "path",
        "url": "filament-excel"
    }
]
```

Now, run `composer update`.

## Credits
This package is based on the excellent [Laravel Nova Excel Package](https://docs.laravel-excel.com/nova/1.x/exports) by SpartnerNL and ported to [Filament](https://filamentadmin.com/).
