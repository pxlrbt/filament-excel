# Filament Excel

Easy Excel exports for Filament Admin.

## Installation

Install via Composer. This will download the package and [Laravel Excel](https://laravel-excel.com/).

```bash
composer install pxlrbt/filament-excel
```

## Usage

Go to your Filament resource and add the `ExportAction` to the tables bulk actions:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Actions\ExportAction;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Maatwebsite\Excel\Excel;

class User extends Resource
{  
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //   
            ])
            ->bulkActions([
                ExportAction::make('export')
            ]);
    }
}
```

Optionally configure your export:

```php
    ExportAction::make('export')
        ->label('Export Data') // Button label
        ->withWriterType(Excel::CSV) // Export type: CSV, XLS, XLSX
        ->except('password') // Exclude fields
        ->withFilename('test') // Set a filename
        ->withHeadings() // Get headings from table or form
        ->withHeadings(['ID', 'E-Mail']) // Or set headings explicitly
        ->askForFilename(date('Y-m-d') . '-export') // Let the user choose a filename. You may pass a default.
        ->askForWriterType(Excel::XLS)  // Let the user choose an export type. You may pass a default.
        ->allFields() // Export all fields on model
        ->onlyTableFields(), // Export only fields from table (Default)
        ->onlyFormFields(),  // Export only fields from form
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
