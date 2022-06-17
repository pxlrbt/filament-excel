# Filament Excel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pxlrbt/filament-excel.svg?include_prereleases)](https://packagist.org/packages/pxlrbt/filament-excel)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/pxlrbt/filament-excel/Code%20Style?label=code%20style)
[![Total Downloads](https://img.shields.io/packagist/dt/pxlrbt/filament-excel.svg)](https://packagist.org/packages/pxlrbt/filament-excel)

Easy Excel exports for Filament.

## Installation

Install via Composer. This will download the package and [Laravel Excel](https://laravel-excel.com/).

**Requires PHP > 8.1 and Filament > 2.0**

```bash
composer require pxlrbt/filament-excel
```


## Quickstart

Starting with v0.2 Filament Excel should work with both `filament/filament` and `filament/tables` packages. The most simple usage is just adding `ExportBulkAction` to your bulk actions.

**Example for admin package**

```php
<?php

namespace App\Filament\Resources;

use pxlrbt\FilamentExcel\Actions\Pages\ExportBulkAction;

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

Many of the functions for customising the export class, accept a Closure that gets passed dynamcic data:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make('table')->withFilename(fn ($resource) => $resource::getLabel()),
])
```

The following arguments are available:
- `$livewire`: Livewire component
- `$resource`: Resource class
- `$recordIds`: IDs of selected records (Bulk Action)
- `$query`: The builder instance 
- `$model`: Model class

### Setting the filename

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

### Setting the file type

You can set the file type via `->withWriterType()`:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()->withWriterType(\Maatwebsite\Excel\Excel::XLSX),
])
```


### Defining fields

When using `->formForm()`/`->formTable()`/`->formModel()` the fields are resolved from your table or form definition. You can also provide fields manually or overwrite resolved fields:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()->withColumns([
        'name', 'created_at', 'deleted_at',
    ]),
])
```


You can also include only a subset of fields (`->only()`) or exclude certain ones (`->except()`):

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

When you neither pass `->only()` nor `->except()` the export will also respect the `$hidden` fields of your model, for example the `password` on the user model. You can disable this by passing an empty array `->except([])`.

### Setting headings

When using `->formForm()`/`->formTable()`/`->formModel()` the headings are resolved from your table or form definition. You can also provide headings manually or overwrite resolved headings:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()->withHeadings([
        'name' => 'Name',
        'email' => 'E-Mail',
        'created_at' => 'Creation Date'
    ]),
])
```

### Ask the user for input

You can let the user pick a filename and writer type by using `->askForFilename()` and `->askForWriterType()`:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()
        ->askForFilename()
## Custom exports

If you need even more customization you can extend the Excel class and implement your own logic.

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

### Queued exports

Exports for resources with many entries can be queued with `->queue()`. They will be processed in a background job and the user will be notified with a notification on the next page load (or when Livewire is polling). 

The temporary file will be deleted after the first download. Files that are not downloaded will be deleted by a scheduled command after 24 hours.

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

ExportAction::make()->exports([
    ExcelExport::make()->queue()
])
```

## Custom exports

If you need even more customization you can extend the Excel class and implement your own logic.


## Example

```php
    use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
    use pxlrbt\FilamentExcel\Exports\ExcelExport;
    
    ExportAction::make('export')
        ->label('Exports Data')
        ->exports([
            ExcelExport::make('export_1')
                ->label('All Fields')                
                ->fromModel()
                ->withHeadings([
                    'id' => 'ID',
                    'created_at' => 'Date created'
                ])      
                ->except('password')
                ->withFilename(fn ($resource) => date('Y-m-d') . '-' . $resource::getLabel()),
                
            ExcelExport::make('export_2')
                ->label('Current table')
                ->fromTable()
                ->withColumns('created_at')
                ->withHeadings(['created_at' => 'Date created')
                ->askForFilename()
                ->askForWriterType(),
                
            ExcelExport::make('export_3')
                ->label('Resource form')
                ->fromForm()
                ->askForFilename(date('Y-m-d') . '-export')
                ->askForWriterType(Maatwebsite\Excel\Excel::CSV),
                
        ])        
    ]);
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
