<?php

namespace pxlrbt\FilamentExcel;

use Illuminate\Support\Facades\URL;
use pxlrbt\FilamentExcel\Interfaces\GeneratesUrl;

class FilamentExcelUrlGenerator implements GeneratesUrl
{
    public function generateUrl(array $export): string
    {
        return URL::temporarySignedRoute(
            'filament-excel-download',
            now()->addHours(24),
            ['path' => $export['filename']]
        );
    }
}
