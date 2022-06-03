# Filament Excel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pxlrbt/filament-excel.svg?include_prereleases)](https://packagist.org/packages/pxlrbt/filament-excel)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/pxlrbt/filament-excel/Code%20Style?label=code%20style)
[![Total Downloads](https://img.shields.io/packagist/dt/pxlrbt/filament-excel.svg)](https://packagist.org/packages/pxlrbt/filament-excel)

Easy Excel exports for Filament Admin.

## Installation

Install via Composer. This will download the package and [Laravel Excel](https://laravel-excel.com/).

**Requires PHP > 8.1 and Filament > 2.0**

```bash
composer require pxlrbt/filament-excel
```


## Quickstart

### Admin package 
Go to your Filament resource and add the `ExportBulkAction` to the tables bulk actions:

```php
<?php

namespace App\Filament\Resources;

use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Export\BulkExport;

class User extends Resource
{  
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //   
            ])
            ->bulkActions([
                ExportBulkAction::make('export')
            ]);
    }
}
```
### Table package

```php
<?php

namespace App\Filament\Resources;

use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Export\BulkExport;

$table
    ->columns([
        //   
    ])
    ->bulkActions([
        ExportTableBulkAction::make('export')
    ]);
```

## Usage

### Use the action

### Configure Exports

### Set filename

## Examples

```php
    use pxlrbt\FilamentExcel\Export;
    use pxlrbt\FilamentExcel\Actions;
    
    Actions\ExportBulkAction::make('export')
        ->label('Export Data')
        ->exportables([
            Export\BulkExport::make('export_1')
                ->label('All Fields')                
                ->fromModel()
                ->withHeadings([
                    'id' => 'ID',
                    'created_at' => 'Date created'
                ])      
                ->except('password')
                ->withFilename(fn ($resource) => date('Y-m-d') . '-' . $resource::getLabel()),
                
            Export\BulkExport::make('export_2')
                ->label('Current table')
                ->fromTable()
                ->withFields('created_at')
                ->withHeadings(['created_at' => 'Date created')
                ->askForFilename()
                ->askForWriterType(),
                
            Export\BulkExport::make('export_3')
                ->label('Resource form')
                ->fromForm()
                ->askForFilename(date('Y-m-d') . '-export')
                ->askForWriterType(Maatwebsite\Excel\Excel::CSV),
                
        ])        
    ]);
```

### Custom exports

If you need even more customization you can use a extend any of the base export classes, e.g.:

```php
use pxlrbt\FilamentExcel\Export;

YourAction extends Export\BulkExport
{
    
}
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
